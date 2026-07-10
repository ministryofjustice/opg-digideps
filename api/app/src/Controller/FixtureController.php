<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Common\Validating\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @phpstan-type FixtureReport array{id: int}
 * @phpstan-type FixtureOrder array{courtOrderUid: string, caseNumber: string, reports: array<FixtureReport>}
 * @phpstan-type FixtureUser array{email: string}
 * @phpstan-type Order array{order: CourtOrder, reports: array<Report>}
 * @phpstan-type OrderPair array<'pfa'|'hw', Order>
 */
class FixtureController extends AbstractController
{
    public function __construct(
        private readonly FixtureService $fixtureService
    ) {
    }

    /**
     * Creates a lay deputy with user account, a single submitted report, and an incomplete
     * current report
     *
     *  Expects body to contain:
     *  - deputyReference (string used in email etc.)
     *  - reportType ("OPG102", "OPG103", "OPG104"; defaults to "OPG103")
     *
     * @return array{users: array<string, FixtureUser>, orders: array<'pfa'|'hw', FixtureOrder>}
     * @throws ValidationException
     */
    #[Route('/fixtures/scenarios/laysimple', name: 'fixtures_scenarios_laysimple', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function scenarioLaySimple(Request $request): array
    {
        $payload = new ValidatingArray($request->getPayload()->all());

        $deputyReference = $payload->getStringOrThrow('deputyReference');
        $reportTypeStr = $payload->getStringOrNull('reportType') ?? '';
        $reportType = CourtOrderReportType::tryFrom($reportTypeStr) ?? CourtOrderReportType::OPG103;

        $details = $this->fixtureService->instantiateScenario(
            new Scenario(
                new CourtOrderDescriptor(
                    new DeputySet(
                        new DeputyDescriptor($deputyReference)
                    ),
                    $reportType,
                    submittedReports: 1
                )
            )
        );

        return $this->jsonifyScenario($details);
    }

    /**
     * Creates a lay deputy with user account and a complete but unsubmitted report
     *
     * @return array{users: array<string, FixtureUser>, orders: array<'pfa'|'hw', FixtureOrder>}
     * @throws ValidationException
     */
    #[Route('/fixtures/scenarios/layreadytosubmit', name: 'fixtures_scenarios_layreadytosubmit', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function scenarioLayReadyToSubmit(Request $request): array
    {
        $payload = new ValidatingArray($request->getPayload()->all());

        $deputyReference = $payload->getStringOrThrow('deputyReference');
        $reportTypeStr = $payload->getStringOrNull('reportType') ?? '';
        $reportType = CourtOrderReportType::tryFrom($reportTypeStr) ?? CourtOrderReportType::OPG102;

        // we set the madeDate to one year ago, so that submission of the report is permitted
        // (otherwise the report submission is blocked because it's not close enough to the due date)
        $details = $this->fixtureService->instantiateScenario(
            new Scenario(
                new CourtOrderDescriptor(
                    new DeputySet(
                        new DeputyDescriptor($deputyReference)
                    ),
                    $reportType,
                    latestReportReadyToSubmit: true,
                    madeDate: new \DateTime()->sub(new \DateInterval('P1Y'))
                )
            )
        );

        return $this->jsonifyScenario($details);
    }

    private function jsonifyScenario(array $details): ?array
    {
        [
            'client' => $client,
            'orders' => $orderPairs,
            'persons' => [
                'users' => $users,
                'deputies' => $deputies,
                'organisations' => $organisations,
            ],
        ] = $details;

        $fixtureUsers = array_map(function ($user) {
            return ['email' => $user->getEmail()];
        }, $users);

        $fixtureOrders = [];
        foreach ($orderPairs as $orderPair) {
            foreach (['pfa', 'hw'] as $orderType) {
                $order = $orderPair[$orderType] ?? null;

                if ($order !== null) {
                    $fixtureOrders[] = [
                        'courtOrderUid' => $order['order']->getCourtOrderUid(),
                        'caseNumber' => $client->getCaseNumber(),
                        'reports' => array_map(fn ($report) => ['id' => $report->getId()], $order['reports']),
                    ];
                }
            }
        }

        return [
            'users' => $fixtureUsers,
            'orders' => $fixtureOrders,
        ];
    }
}

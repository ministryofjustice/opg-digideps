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

class FixtureController extends AbstractController
{
    public function __construct(
        private readonly FixtureService $fixtureService
    ) {
    }

    /**
     * Creates a lay deputy with user account and a single submitted report
     *
     * @phpstan-type FixtureReport array{id: int}
     * @phpstan-type FixtureOrder array{courtOrderUid: string, reports: array<FixtureReport>}
     * @phpstan-type FixtureUser array{email: string}
     * @return array{users: array<string, FixtureUser>, orders: array<'pfa'|'hw', FixtureOrder>}
     * @throws ValidationException
     */
    #[Route('/fixtures/scenarios/simplelay', name: 'fixtures_scenarios_simplelay', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function scenarioSimpleLay(Request $request): array
    {
        $payload = new ValidatingArray($request->getPayload()->all());

        $deputyReference = $payload->getStringOrThrow('deputyReference');
        $reportTypeStr = $payload->getStringOrNull('reportType') ?? '';
        $reportType = CourtOrderReportType::tryFrom($reportTypeStr) ?? CourtOrderReportType::OPG103;

        // create a user and deputy for the court order
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
     * @phpstan-type Order array{order: CourtOrder, reports: array<Report>}
     * @phpstan-type OrderPair array<'pfa'|'hw', Order>
     */
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
                        'reports' => array_map(fn($report) => ['id' => $report->getId()], $order['reports']),
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

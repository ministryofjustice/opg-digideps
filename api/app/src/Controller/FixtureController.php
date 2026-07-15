<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Common\Validating\ValidationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @phpstan-type FixtureReport array{id: int, documents: array<FixtureDocument>}
 * @phpstan-type FixtureDocument array{id: int}
 * @phpstan-type FixtureOrder array{courtOrderUid: string, caseNumber: ?string, reports: array<FixtureReport>}
 * @phpstan-type FixtureUser array{email: string}
 * @phpstan-type Order array{order: CourtOrder, reports: array<Report>}
 * @phpstan-type OrderPair array<'pfa'|'hw', Order>
 * @phpstan-type FixtureJson array{users: array<FixtureUser>, orders: array<FixtureOrder>}
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
     * @return FixtureJson
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
     * @return FixtureJson
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

    /**
     * Creates a lay deputy with user account and a complete but unsubmitted report;
     * the report has documents attached but those documents have no objects in S3.
     *
     * @return FixtureJson
     * @throws ValidationException|\DateInvalidOperationException
     */
    #[Route('/fixtures/scenarios/layreadytosubmit/expireds3objects', name: 'fixtures_scenarios_layreadytosubmit_expireds3objects', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function scenarioLayReadyToSubmitExpiredS3Objects(Request $request): array
    {
        $payload = new ValidatingArray($request->getPayload()->all());

        $deputyReference = $payload->getStringOrThrow('deputyReference');
        $reportTypeStr = $payload->getStringOrNull('reportType') ?? '';

        /** @var string[] $supportingDocumentNames */
        $supportingDocumentNames = $payload->getArrayOrDefault('supportingDocumentNames', ['receipt1.pdf', 'receipt2.pdf']);

        $reportType = CourtOrderReportType::tryFrom($reportTypeStr) ?? CourtOrderReportType::OPG102;

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

        // TODO refactor into a ReportsSet on a court order
        // for each report on each court order, add all uploaded files to it, none with a backing S3 object
        foreach ($details['orders'] as $orderPair) {
            foreach (['pfa', 'hw'] as $orderType) {
                $order = $orderPair[$orderType] ?? null;

                if ($order !== null) {
                    foreach ($order['reports'] as $report) {
                        foreach ($supportingDocumentNames as $uploadedFile) {
                            $this->fixtureService->addSupportingDocumentWithoutS3Object($report, $uploadedFile);
                        }
                    }
                }
            }
        }

        return $this->jsonifyScenario($details);
    }

    /**
     * @return FixtureJson
     */
    private function jsonifyScenario(array $details): array
    {
        /** @var OrderPair[] $orderPairs */
        $orderPairs = $details['orders'] ?? [];

        /** @var User[] $users */
        $users = $details['users'] ?? [];

        /** @var Client $client */
        $client = $details['client'] ?? null;
        if ($client === null) {
            throw new \DomainException('Scenario did not generate a client');
        }

        $fixtureUsers = array_map(fn ($user) => ['email' => $user->getEmail()], $users);

        $fixtureOrders = [];

        /** @var OrderPair $orderPair */
        foreach ($orderPairs as $orderPair) {
            foreach (['pfa', 'hw'] as $orderType) {
                /** @var array{order: CourtOrder, reports: Report[]} $order */
                $order = $orderPair[$orderType] ?? null;

                if ($order !== null) {
                    $reports = array_map(fn ($report) => [
                        'id' => $report->getId(),
                        'documents' => array_map(fn ($document) => ['id' => $document->getId()], $report->getDocuments()->toArray()),
                    ], $order['reports']);

                    /** @var CourtOrder $courtOrder */
                    $courtOrder = $order['order'];

                    $fixtureOrders[] = [
                        'courtOrderUid' => $courtOrder->getCourtOrderUid(),
                        'caseNumber' => $client->getCaseNumber(),
                        'reports' => $reports,
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

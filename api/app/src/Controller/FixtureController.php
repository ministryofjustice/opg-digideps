<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Controller;

use OPG\Digideps\Backend\Domain\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputyDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\FixtureService;
use OPG\Digideps\Backend\Fixture\Scenario;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FixtureController
{
    public function __construct(
        private readonly FixtureService $fixtureService
    ) {
    }

    /**
     * @return array{clientId: int}
     */
    #[Route('/fixtures/scenarios/simplelay', name: 'fixtures_scenarios_simplelay', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function scenarioSimpleLay(): array
    {
        // TODO get from request
        $deputyReference = 'lay1';

        // TODO get from request
        $reportType = CourtOrderReportType::OPG103;

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

    private function jsonifyScenario(array $details): array
    {
        [
            'client' => $client,
            'orders' => $orders,
            'persons' => $persons,
        ] = $details;

        return ['clientId' => $client->getId()];
    }
}

<?php

declare(strict_types=1);

namespace App\FixtureFactory;

use App\Factory\ReportEntityFactory;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\ReportTestHelper;
use App\TestHelpers\UserTestHelper;
use Doctrine\ORM\EntityManager;

class LoadTestFactory
{
    public function __construct(
        private EntityManager $em,
        private ReportEntityFactory $reportEntityFactory
    ) {
    }

    /**
     * Use to persist entities to simulate a prod like databases in size (run in a test for a hacky way to fill the DB).
     */
    public function createUsersClientsReports(int $recordsToMake)
    {
        $oneYearAgo = (new \DateTimeImmutable())->modify('-1 Year');

        $userTestHelper = new UserTestHelper();
        $reportTestHelper = new ReportTestHelper($this->reportEntityFactory);
        $clientTestHelper = new ClientTestHelper();

        foreach (range(1, $recordsToMake) as $index) {
            $user = $userTestHelper->createUser(null)
                ->setLastLoggedIn(
                    \DateTime::createFromImmutable($oneYearAgo->modify('+1 day'))
                );

            $user->setEmail($user->getEmail().rand(1, 100000));

            $client = $clientTestHelper->generateClient($this->em, $user);
            $user->addClient($client);

            $report = $reportTestHelper->generateReport($this->em, $user->getFirstClient());
            $report->setSubmitDate(new \DateTime());

            $client->addReport($report);

            $this->em->persist($user);
            $this->em->persist($client);
            $this->em->persist($report);
        }

        $this->em->flush();
    }
}

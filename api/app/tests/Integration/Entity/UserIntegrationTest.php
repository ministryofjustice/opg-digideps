<?php

namespace Tests\OPG\Digideps\Backend\Integration\Entity;

use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Fixture\CourtOrderDescriptor;
use OPG\Digideps\Backend\Fixture\DeputySet;
use OPG\Digideps\Backend\Fixture\ReportList;
use OPG\Digideps\Backend\Fixture\Scenario;
use Tests\OPG\Digideps\Backend\Integration\ApiIntegrationTestCase;

class UserIntegrationTest extends ApiIntegrationTestCase
{
    public function testGetNumberOfSubmittedReports()
    {
        $this->purgeDatabase(['dd_user']);

        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports(submittedReports: 2))));
        $submittedSubmissions1 = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");

        ['orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(new Scenario(new CourtOrderDescriptor(DeputySet::oneLay(), reportList: ReportList::manyReports())));
        $submittedSubmissions2 = $report->getReportSubmissions()->first() ?: throw new \LogicException("This must exist");

        // Create a report submission but don't submit it
        ['persons' => ['users' => ['lay1' => $user]], 'orders' => [['pfa' => ['reports' => [$report]]]]] = self::$fixtureService->instantiateScenario(Scenario::newSimpleLayScenario());
        $notSubmittedSubmission = new ReportSubmission($report, $user);
        $notSubmittedSubmission->setCreatedOn(new \DateTime());
        self::$fixtureService->persist($notSubmittedSubmission);

        $user1 = $submittedSubmissions1->getReport()->getClient()->getUsers()->first();
        $user2 = $submittedSubmissions2->getReport()->getClient()->getUsers()->first();
        $user3 = $notSubmittedSubmission->getReport()->getClient()->getUsers()->first();
        $this->assertTrue($user1 && $user2 && $user3);

        self::$fixtureService->flush();
        self::$entityManager->clear();

        self::assertSame(2, $this->reloadUser($user1)->getNumberOfSubmittedReports());
        self::assertSame(1, $this->reloadUser($user2)->getNumberOfSubmittedReports());
        self::assertSame(0, $this->reloadUser($user3)->getNumberOfSubmittedReports());
    }

    private function reloadUser(User $user): User
    {
        return self::$entityManager->getRepository(User::class)->find($user->getId()) ?? throw new \LogicException("We know this exists");
    }
}

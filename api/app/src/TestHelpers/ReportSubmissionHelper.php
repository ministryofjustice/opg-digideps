<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Report\ReportSubmission;
use App\Factory\ReportEntityFactory;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReportSubmissionHelper extends KernelTestCase
{
    public function __construct(private ReportEntityFactory $reportEntityFactory)
    {
        parent::__construct();
    }

    /**
     * @return ReportSubmission
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateAndPersistReportSubmission(EntityManager $em)
    {
        $client = new Client();
        $report = (new ReportTestHelper($this->reportEntityFactory))->generateReport($em, $client, null, new \DateTime());
        $client->addReport($report);
        $user = (new UserTestHelper())->createAndPersistUser($em, $client);
        $reportSubmission = (new ReportSubmission($report, $user))->setCreatedOn(new \DateTime());

        $em->persist($client);
        $em->persist($report);
        $em->persist($user);
        $em->persist($reportSubmission);
        $em->flush();

        return $reportSubmission;
    }

    public function generateAndPersistSubmittedReportSubmission(EntityManager $em, \DateTime $submitDate)
    {
        $rs = $this->generateAndPersistReportSubmission($em);
        $report = $rs->getReport()
            ->setSubmitDate($submitDate)
            ->setSubmitted(true);
        $rs->setCreatedOn($submitDate);

        $em->persist($rs);
        $em->persist($report);
        $em->flush();

        return $rs;
    }

    public function submitAndPersistAdditionalSubmissions(EntityManager $em, ReportSubmission $lastSubmission)
    {
        $client = $lastSubmission->getReport()->getClient();

        $report = (new ReportTestHelper($this->reportEntityFactory))->generateReport(
            $em,
            $client,
            $lastSubmission->getReport()->getType(),
            $lastSubmission->getReport()->getSubmitDate()->modify('+366 days')
        );

        $client->addReport($report);

        $reportSubmission = (new ReportSubmission($report, $lastSubmission->getReport()->getClient()->getUsers()[0]))
        ->setCreatedOn(new \DateTime('+366 days'));

        $report
            ->setSubmitDate(new \DateTime('+366 days'))
            ->setSubmitted(true)
            ->setClient($client);

        $em->persist($client);
        $em->persist($report);
        $em->persist($reportSubmission);

        $em->flush();
    }
}

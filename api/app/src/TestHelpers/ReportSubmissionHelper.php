<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Report\ReportSubmission;
use Doctrine\ORM\EntityManager;

class ReportSubmissionHelper
{
    public function __construct(
        private readonly EntityManager $entityManager
    ) {
    }

    /**
     * @return ReportSubmission
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function generateAndPersistReportSubmission()
    {
        $client = new Client();
        $report = (new ReportTestHelper())->generateReport($this->entityManager, $client, null, new \DateTime());
        $client->addReport($report);
        $user = (UserTestHelper::create())->createAndPersistUser($this->entityManager, $client);
        $reportSubmission = new ReportSubmission($report, $user);
        $reportSubmission->setCreatedOn(new \DateTime());

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
        $this->entityManager->persist($user);
        $this->entityManager->persist($reportSubmission);
        $this->entityManager->flush();

        return $reportSubmission;
    }

    public function generateAndPersistSubmittedReportSubmission(\DateTime $submitDate)
    {
        $rs = $this->generateAndPersistReportSubmission();
        $report = $rs->getReport();

        if (null === $report) {
            throw new \LogicException('Generated ReportSubmission was created without a report');
        }

        $report->setSubmitDate($submitDate)
            ->setSubmitted(true);
        $rs->setCreatedOn($submitDate);

        $this->entityManager->persist($rs);
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        return $rs;
    }

    public function submitAndPersistAdditionalSubmissions(ReportSubmission $lastSubmission)
    {
        $existingReport = $lastSubmission->getReport();

        if (null === $existingReport) {
            throw new \LogicException('Report submission was created without a report');
        }

        $client = $existingReport->getClient();

        $report = (new ReportTestHelper())->generateReport(
            $this->entityManager,
            $client,
            $existingReport->getType(),
            $existingReport->getSubmitDate()->modify('+366 days')
        );

        $client->addReport($report);

        $reportSubmission = (new ReportSubmission($report, $client->getUsers()[0]))
            ->setCreatedOn(new \DateTime('+366 days'));

        $report
            ->setSubmitDate(new \DateTime('+366 days'))
            ->setSubmitted(true)
            ->setClient($client);

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
        $this->entityManager->persist($reportSubmission);

        $this->entityManager->flush();
    }
}

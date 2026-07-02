<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\TestHelpers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Entity\User;

class ReportSubmissionHelper
{
    public function __construct(
        private readonly EntityManager $entityManager
    ) {}

    /**
     * @return ReportSubmission
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function generateAndPersistReportSubmission(): ReportSubmission
    {
        $client = new Client();
        $report = new ReportTestHelper()->generateReport($this->entityManager, $client, null, new \DateTime());
        $client->addReport($report);
        $user = UserTestHelper::create()->createAndPersistUser($this->entityManager, $client);
        $reportSubmission = new ReportSubmission($report, $user);
        $reportSubmission->setCreatedOn(new \DateTime());

        $this->entityManager->persist($client);
        $this->entityManager->persist($report);
        $this->entityManager->persist($user);
        $this->entityManager->persist($reportSubmission);
        $this->entityManager->flush();

        return $reportSubmission;
    }

    public function generateAndPersistSubmittedReportSubmission(\DateTime $submitDate): ReportSubmission
    {
        $rs = $this->generateAndPersistReportSubmission();
        $report = $rs->getReport();

        if ($report === null) {
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

    public function submitAndPersistAdditionalSubmissions(ReportSubmission $lastSubmission): void
    {
        $existingReport = $lastSubmission->getReport();

        if ($existingReport === null) {
            throw new \LogicException('Report submission was created without a report');
        }
        $submissionDate = $existingReport->getSubmitDate();
        if ($submissionDate === null) {
            throw new \LogicException('Report submission date is not set yet there is a submission');
        }

        $client = $existingReport->getClient();

        $report = new ReportTestHelper()->generateReport(
            $this->entityManager,
            $client,
            $existingReport->getType(),
            $submissionDate->modify('+366 days')
        );

        $client->addReport($report);

        $submitter = $client->getUsers()->first();
        if (!($submitter instanceof User)) {
            throw new \LogicException('Report submission cannot be created with a submitting user');
        }

        $reportSubmission = new ReportSubmission($report, $client->getUsers()[0])
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

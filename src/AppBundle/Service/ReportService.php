<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Asset;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Report\BankAccount as ReportBankAccount;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;

class ReportService
{
    /** @var EntityRepository */
    protected $reportRepository;

    /**
     * @var CasRecRepository
     */
    private $casRecRepository;

    public function __construct(
        EntityManager $em
    )
    {
        $this->reportRepository = $em->getRepository(Report::class);
        $this->casRecRepository = $em->getRepository(CasRec::class);
        $this->_em = $em;
        $this->assetRepository = $em->getRepository(Asset::class);
        $this->bankAccountRepository = $em->getRepository(BankAccountEntity::class);
    }

    public function findById($id)
    {
        return $this->reportRepository->findOneBy(['id' => $id]);
    }

    /**
     * Set report type based on CasRec record (if existing)
     *
     * @param Client $report
     *
     * @return string|null type
     */
    public function getReportTypeBasedOnCasrec(Client $client)
    {
        $casRec = $this->casRecRepository->findOneBy(['caseNumber' => $client->getCaseNumber()]);
        if ($casRec instanceof CasRec) {
            return CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref(), $client->getUsers()->first()->getRoleName());
        }

        return null;
    }

    /**
     * Create new year's report copying data over (and set start/endDate accordingly).
     *
     * @param Report $oldReport
     *
     * @return Report
     */
    private function createNextYearReport(Report $oldReport)
    {
        if (!$oldReport->getSubmitted()) {
            throw new \RuntimeException("Can't create a new year report based on an unsubmitted report");
        }

        $client = $oldReport->getClient();

        $startDate = clone $oldReport->getEndDate();
        $startDate->modify('+1 day');

        $endDate = clone $startDate;
        $endDate->modify('+12 months -1 day');

        $newReport = new Report(
            $client,
            $this->getReportTypeBasedOnCasrec($client) ?: $oldReport->getType(), // report comes from casrec, or last year report, if not found
            $startDate,
            $endDate,
            false
        );

        // copy assets
        $newReport->setNoAssetToAdd($oldReport->getNoAssetToAdd());
        foreach ($oldReport->getAssets() as $asset) {
            $newAsset = clone $asset;
            $newAsset->setReport($newReport);
            $this->_em->detach($newAsset);
            $this->_em->persist($newAsset);
        }

        // copy bank accounts (opening balance = closing balance, opening date = closing date)
        foreach ($oldReport->getBankAccounts() as $account) {
            $newAccount = new ReportBankAccount();
            $newAccount->setBank($account->getBank());
            $newAccount->setAccountType($account->getAccountType());
            $newAccount->setSortCode($account->getSortCode());
            $newAccount->setAccountNumber($account->getAccountNumber());
            $newAccount->setOpeningBalance($account->getClosingBalance());
            $newAccount->setCreatedAt(new \DateTime());
            $newAccount->setReport($newReport);

            $this->_em->persist($newAccount);
        }

        $newReport->updateSectionsStatusCache($newReport->getAvailableSections());
        $this->_em->persist($newReport);

        return $newReport;
    }

    /**
     * Using an array of CasRec entities update any corresponding report type if it has been changed
     *
     * @param array $casRecEntities
     * @param string $userRoleName
     *
     * @throws \Exception
     */
    public function updateCurrentReportTypes(array $casRecEntities, $userRoleName)
    {
        //  Check the contents of the entities array and check the integrity of the components
        $casRecEntitiesWithKey = [];

        foreach ($casRecEntities as $CasRec) {
            if (!$CasRec instanceof CasRec) {
                throw new \Exception('Invalid casrec entity encountered. AppBundle\Entity\CasRec expected');
            }

            $casRecEntitiesWithKey[$CasRec->getCaseNumber()] = $CasRec;
        }

        //  Create a case numbers string from the keys
        $caseNumbersString = '\'' . implode('\',\'', array_keys($casRecEntitiesWithKey)) . '\'';

        //  Use the case numbers to get any existing reports (not submitted)
        $qb = $this->reportRepository->createQueryBuilder('r');

        $qb->leftJoin('r.client', 'c')
            ->where('(r.submitted = false OR r.submitted is null) AND c.caseNumber IN (' . $caseNumbersString . ')');

        $reports = $qb->getQuery()->getResult();
        /* @var $reports Report[] */

        //  Loop through the reports and update the report type if necessary
        foreach ($reports as $report) {
            $reportClientCaseNumber = $report->getClient()->getCaseNumber();

            if (isset($casRecEntitiesWithKey[$reportClientCaseNumber])) {
                //  Get the report type based on the CasRec record
                $casRec = $casRecEntitiesWithKey[$reportClientCaseNumber];
                $casRecReportType = CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref(), $userRoleName);

                if ($report->getType() != $casRecReportType) {
                    $report->setType($casRecReportType);
                    $this->_em->persist($report);
                }
            }
        }

        $this->_em->flush();
    }

    /**
     * Set report submitted and create a new year report
     *
     * @param Report|Ndr $currentReport
     * @param User $user
     * @param \DateTime $submitDate
     *
     * @return Report new year's report
     */
    public function submit($currentReport, User $user, \DateTime $submitDate)
    {
        if (!$currentReport->getAgreedBehalfDeputy()) {
            throw new \RuntimeException('Report must be agreed for submission');
        }

        // update report submit flag, who submitted and date
        $currentReport
            ->setSubmitted(true)
            ->setSubmittedBy($user)
            ->setSubmitDate($submitDate);

        // create submission record with NEW documents (= documents not yet attached to a submission)
        $submission = new ReportSubmission($currentReport, $user);
        foreach ($currentReport->getDocuments() as $document) {
            if (!$document->getReportSubmission()) {
                $document->setReportSubmission($submission);
            }
        }
        $this->_em->persist($submission);

        if ($currentReport->getUnSubmitDate()) {
            //unsubmitted report
            $currentReport->setUnSubmitDate(null);
            $currentReport->setUnsubmittedSectionsList(null);
            $newYearReport = null;
        } else {
            // first-time submission
            $newYearReport = $this->createNextYearReport($currentReport);
        }

        $this->_em->flush(); // single transaction for report.submitted flags + new year report creation

        return $newYearReport;
    }

    /**
     * @param Report $report
     * @param \DateTime $unsubmitDate
     * @param \DateTime $dueDate
     * @param $sectionList
     */
    public function unSubmit(Report $report, \DateTime $unsubmitDate, \DateTime $dueDate, \DateTime $startDate, \DateTime $endDate, $sectionList)
    {
        // reset report.submitted so that the deputy will set the report back into the dashboard
        $report->setSubmitted(false);

        $report->setUnSubmitDate($unsubmitDate);
        $report->setDueDate($dueDate);
        $report->setStartDate($startDate);
        $report->setEndDate($endDate);

        $report->setUnsubmittedSectionsList($sectionList);

        $this->_em->flush();
    }

    /**
     * Set report submission for additional documents
     *
     * @param Report $currentReport
     * @param User $user
     * @param \DateTime $submitDate
     *
     * @return Report new year's report
     */
    public function submitAdditionalDocuments(Report $currentReport, User $user, \DateTime $submitDate)
    {
        // create submission record with NEW documents (= documents not yet attached to a submission)
        $submission = new ReportSubmission($currentReport, $user);
        foreach ($currentReport->getDocuments() as $document) {
            if (!$document->getReportSubmission()) {
                $document->setReportSubmission($submission);
            }
        }

        $this->_em->persist($submission);

        // single transaction flush: current report, submission, new year report
        $this->_em->flush();

        return $currentReport;
    }

    /**
     * If one report started, return the other nonStarted reports with the same start/end date
     *
     *
     * @param Collection $reports
     *
     * @return Report[] indexed by ID
     */
    public function findDeleteableReports(Collection $reports)
    {
        $reportIdToStatus = [];
        foreach ($reports as $ur) {
            /* @var $ur Report */
            $reportIdToStatus[$ur->getId()] = [
                'status' => $ur->getStatus()->getStatus(),
                'start' => $ur->getStartDate()->format('Y-m-d'),
                'end' => $ur->getEndDate()->format('Y-m-d'),
                'sections' => $ur->getStatus()->getSectionStatus()
            ];
        }

        $ret = [];
        foreach ($reports as $report1) {
            /* @var $report1 Report */
            foreach ($reports as $report2) {
                /* @var $report2 Report */
                if ($report1->getId() === $report2->getId()) {
                    continue;
                }
                // find report with same date that have not started
                if ($report1->getStatus()->hasStarted()
                    && $report1->hasSamePeriodAs($report2)
                    && !$report2->getStatus()->hasStarted()) {
                    $ret[$report2->getId()] = $report2;
                }
            }
        }

        return $ret;
    }


    /**
     * @param $select reports|count
     * @param $status
     * @param $userId
     * @param $exclude_submitted
     * @param $q
     *
     * @return QueryBuilder
     */
    public function getAllReportsQb($select, $status, $userId, $exclude_submitted, $q)
    {
        $qb = $this->_em->getRepository(Report::class)
            ->createQueryBuilder('r');

        if ($select == 'reports') {
            $qb
                ->select('r,c,u')
                ->leftJoin('r.submittedBy', 'sb');
        } elseif ($select == 'count') {
            $qb->select('COUNT(r)');
        } else {
            throw new \InvalidArgumentException(__METHOD__ . ": first must be reports|count");
        }

        $qb
            ->leftJoin('r.client', 'c')
            ->leftJoin('c.users', 'u')
            ->where('u.id = ' . $userId);

        if ($exclude_submitted) {
            $qb->andWhere('r.submitted = false OR r.submitted is null');
        }

        if ($q) {
            $qb->andWhere('lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike OR c.caseNumber = :q');
            $qb->setParameter('qLike', '%' . strtolower($q) . '%');
            $qb->setParameter('q', $q);
        }

        $qb->andWhere('r.reportStatusCached = :status')
            ->setParameter('status', $status);

        // Since reportStatusCached is stored ignoring due date, an additional filter is needed
        if ($status == Report::STATUS_READY_TO_SUBMIT) {
            $qb->andWhere('r.endDate <= :today')
                ->setParameter('today', new \DateTime('today midnight'));
        }

        return $qb;
    }

}

<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
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
use Doctrine\ORM\QueryBuilder;

class ReportService
{
    /** @var EntityRepository */
    protected $reportRepository;

    /**
     * @var EntityManager
     */
    protected $_em;

    /**
     * @var EntityRepository
     */
    private $casRecRepository;

    /**
     * @var EntityRepository
     */
    private $assetRepository;

    /**
     * @var EntityRepository
     */
    private $bankAccountRepository;

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

        $this->clonePersistentResources($newReport, $oldReport);

        $newReport->updateSectionsStatusCache($newReport->getAvailableSections());
        $this->_em->persist($newReport);

        return $newReport;
    }

    /**
     * Clone resources which cross report periods from one account to another
     *
     * @param Ndr|Report $toReport
     * @param Ndr|Report $fromReport
     */
    public function clonePersistentResources($toReport, $fromReport)
    {
        // copy assets
        $toReport->setNoAssetToAdd($fromReport->getNoAssetToAdd());
        foreach ($fromReport->getAssets() as $asset) {
            // Check that the target report doesn't already have a matching asset
            $assetExists = false;
            foreach ($toReport->getAssets() as $toAsset) {
                if ($asset->getType() === 'property') {
                    if ($toAsset->getType() === 'property'
                        && $toAsset->getAddress() === $asset->getAddress()
                        && $toAsset->getAddress2() === $asset->getAddress2()
                        && $toAsset->getPostcode() === $asset->getPostcode()) {
                        $assetExists = true;
                        break;
                    }
                } else {
                    if ($toAsset->getType() === 'other' && $toAsset->getDescription() === $asset->getDescription()) {
                        $assetExists = true;
                        break;
                    }
                }
            }

            if (!$assetExists) {
                $newAsset = clone $asset;
                $newAsset->setReport($toReport);
                $this->_em->detach($newAsset);
                $this->_em->persist($newAsset);
            }
        }

        // copy bank accounts (opening balance = closing balance, opening date = closing date)
        foreach ($fromReport->getBankAccounts() as $account) {
            // Check that the target report doesn't already have a bank account with that account number
            $accountExists = false;
            foreach ($toReport->getBankAccounts() as $toAccount) {
                if ($toAccount->getAccountType() === $account->getAccountType()
                    && $toAccount->getBank() === $account->getBank()
                    && $toAccount->getAccountNumber() === $account->getAccountNumber()
                    && $toAccount->getSortCode() === $account->getSortCode()) {
                    $accountExists = true;
                    break;
                }
            }

            if (!$account->getIsClosed() && !$accountExists) {
                $newAccount = new ReportBankAccount();
                $newAccount->setBank($account->getBank());
                $newAccount->setAccountType($account->getAccountType());
                $newAccount->setSortCode($account->getSortCode());
                $newAccount->setAccountNumber($account->getAccountNumber());
                $newAccount->setOpeningBalance($account->getClosingBalance());
                $newAccount->setIsJointAccount($account->getIsJointAccount());
                $newAccount->setCreatedAt(new \DateTime());
                $newAccount->setReport($toReport);

                $this->_em->persist($newAccount);
            }
        }
    }

    /**
     * Set report submitted and create a new year report
     *
     * @param Ndr|Report $currentReport
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

            // Find the next report and clone assets/accounts across
            $calculatedEndDate = clone $currentReport->getEndDate();
            $calculatedEndDate->modify('+12 months');
            $newYearReport = $currentReport->getClient()->getReportByEndDate($calculatedEndDate);

            if ($newYearReport) {
                $this->clonePersistentResources($newYearReport, $currentReport);
            }
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
     * If the report is ready to submit, but is not yet due, return notFinished instead
     * In all the the cases, return original $status
     *
     * @param $status
     * @param \DateTime $endDate
     *
     * @return string
     */
    public function adjustReportStatus($status, \DateTime $endDate)
    {
        if ($status == Report::STATUS_READY_TO_SUBMIT && !self::isDue($endDate)) {
            return Report::STATUS_NOT_FINISHED;
        }

        return $status;
    }


    /**
     * @param \DateTime|null $endDate
     * @return bool
     */
    public static function isDue(\DateTime $endDate = null)
    {
        if (!$endDate) {
            return false;
        }

        $endOfToday = new \DateTime('today midnight');

        return $endDate <= $endOfToday;
    }

}

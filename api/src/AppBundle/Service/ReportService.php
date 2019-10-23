<?php

namespace AppBundle\Service;

use AppBundle\Entity\AssetInterface;
use AppBundle\Entity\BankAccountInterface;
use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Report\Asset;
use AppBundle\Entity\Ndr\Asset as NdrAsset;
use AppBundle\Entity\Report\AssetProperty as ReportAssetProperty;
use AppBundle\Entity\Report\AssetOther as ReportAssetOther;
use AppBundle\Entity\Ndr\AssetProperty as NdrAssetProperty;
use AppBundle\Entity\Ndr\AssetOther as NdrAssetOther;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Report\BankAccount as ReportBankAccount;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\ReportInterface;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

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
    private function createNextYearReport(ReportInterface $oldReport)
    {
        if (!$oldReport->getSubmitted()) {
            throw new \RuntimeException("Can't create a new year report based on an unsubmitted report");
        }

        $client = $oldReport->getClient();

        if ($oldReport instanceof Report) {
            $startDate = clone $oldReport->getEndDate();
            $newReportType = $this->getReportTypeBasedOnCasrec($client) ?: $oldReport->getType();
        } else {
            // when the previous report is NDR we need to work out the new reporting period
            $startDate = $oldReport->getClient()->getExpectedReportStartDate();
            // set default type as oldReport is ndr
            $newReportType = $this->getReportTypeBasedOnCasrec($client) ?: Report::TYPE_102;
        }
        $startDate->modify('+1 day');

        $endDate = clone $startDate;
        $endDate->modify('+12 months -1 day');

        $newReport = new Report(
            $client,
            $newReportType, // report comes from casrec, or last year report, if not found
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
            $assetExists = $this->checkAssetExists($toReport, $asset);

            if (!$assetExists) {
                $newAsset = $this->cloneAsset($asset);
                $newAsset->setReport($toReport);
                $this->_em->detach($newAsset);
                $this->_em->persist($newAsset);
            }
        }

        // copy bank accounts (opening balance = closing balance, opening date = closing date)
        foreach ($fromReport->getBankAccounts() as $account) {
            // Check that the target report doesn't already have a bank account with that account number
            $accountExists = $this->checkBankAccountExists($toReport, $account);

            if (!$accountExists) {
                $newAccount = $this->cloneBankAccount($toReport, $account);
                $newAccount->setReport($toReport);
                $this->_em->persist($newAccount);
            }
        }
    }

    /**
     * @param ReportInterface $toReport
     * @param $asset
     * @return bool
     */
    private function checkAssetExists(ReportInterface $toReport, AssetInterface $asset)
    {
        foreach ($toReport->getAssets() as $toAsset) {
            if ($toAsset->getType() === 'property'
                && $toAsset->getAddress() === $asset->getAddress()
                && $toAsset->getAddress2() === $asset->getAddress2()
                && $toAsset->getPostcode() === $asset->getPostcode()) {
                return true;
            } else {
                if ($toAsset->getType() === 'other' && $toAsset->getDescription() === $asset->getDescription()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param ReportInterface $toReport
     * @param BankAccountInterface $account
     * @return bool
     */
    private function checkBankAccountExists(ReportInterface $toReport, BankAccountInterface $account)
    {
        foreach ($toReport->getBankAccounts() as $toAccount) {
            if ($toAccount->getAccountType() === $account->getAccountType()
                && $toAccount->getBank() === $account->getBank()
                && $toAccount->getAccountNumber() === $account->getAccountNumber()
                && $toAccount->getSortCode() === $account->getSortCode()
                && !$account->getIsClosed()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Convert NDR asset into Report Asset
     *
     * @param AssetInterface  $asset
     * @return ReportAssetOther|NdrAssetOther|ReportAssetProperty
     */
    private function cloneAsset(AssetInterface $asset)
    {
        if ($asset instanceof NdrAssetProperty ||
            $asset instanceof ReportAssetProperty) {
            $newAsset = new ReportAssetProperty();

            $newAsset->setAddress($asset->getAddress());
            $newAsset->setAddress2($asset->getAddress2());
            $newAsset->setCounty($asset->getCounty());
            $newAsset->setPostcode($asset->getPostcode());
            $newAsset->setOccupants($asset->getOccupants());
            $newAsset->setOwned($asset->getOwned());
            $newAsset->setOwnedPercentage($asset->getOwnedPercentage());
            $newAsset->setIsSubjectToEquityRelease($asset->getIsSubjectToEquityRelease());
            $newAsset->setHasMortgage($asset->getHasMortgage());
            $newAsset->setMortgageOutstandingAmount($asset->getMortgageOutstandingAmount());
            $newAsset->setHasCharges($asset->getHasCharges());
            $newAsset->setIsRentedOut($asset->getIsRentedOut());
            $newAsset->setRentAgreementEndDate($asset->getRentAgreementEndDate());
            $newAsset->setRentIncomeMonth($asset->getRentIncomeMonth());

        } else {
            $newAsset = new ReportAssetOther();
            $newAsset->setTitle($asset->getTitle());
            $newAsset->setDescription($asset->getDescription());
            $newAsset->setValuationDate($asset->getValuationDate());
        }

        $newAsset->setValue($asset->getValue());

        return $newAsset;
    }

    /**
     * Clones instance of ReportInterface and returns new Report Bank Account
     *
     * @param ReportInterface $toReport
     * @param BankAccountInterface $account
     * @return ReportBankAccount
     */
    private function cloneBankAccount(ReportInterface $toReport, BankAccountInterface $account)
    {
        $newAccount = new ReportBankAccount();

        $newAccount->setBank($account->getBank());
        $newAccount->setAccountType($account->getAccountType());
        $newAccount->setSortCode($account->getSortCode());
        $newAccount->setAccountNumber($account->getAccountNumber());
        $newAccount->setOpeningBalance($account->getClosingBalance());
        $newAccount->setIsJointAccount($account->getIsJointAccount());
        $newAccount->setCreatedAt(new \DateTime());

        return $newAccount;
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
    public function submit(ReportInterface $currentReport, User $user, \DateTime $submitDate, $ndrDocumentId = null)
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
        if ($currentReport instanceof Ndr) {
            $document = $this->_em->getRepository(Document::class)->find($ndrDocumentId);
            $document->setReportSubmission($submission);
        } else {
            foreach ($currentReport->getDocuments() as $document) {
                if (!$document->getReportSubmission()) {
                    $document->setReportSubmission($submission);
                }
            }
        }

        $this->_em->persist($submission);

        if ($currentReport instanceof Report && $currentReport->getUnSubmitDate()) {
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

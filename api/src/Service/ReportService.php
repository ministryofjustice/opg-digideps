<?php

namespace App\Service;

use App\Entity\AssetInterface;
use App\Entity\BankAccountInterface;
use App\Entity\CasRec;
use App\Entity\Client;
use App\Entity\Ndr\AssetOther as NdrAssetOther;
use App\Entity\Ndr\AssetProperty as NdrAssetProperty;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Asset;
use App\Entity\Report\AssetOther as ReportAssetOther;
use App\Entity\Report\AssetProperty as ReportAssetProperty;
use App\Entity\Report\BankAccount as BankAccountEntity;
use App\Entity\Report\BankAccount as ReportBankAccount;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Entity\User;
use App\Repository\ReportRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class ReportService
{
    /** @var ReportRepository */
    protected $reportRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    /**
     * @var ObjectRepository
     */
    private $casRecRepository;

    /**
     * @var ObjectRepository
     */
    private $assetRepository;

    /**
     * @var ObjectRepository
     */
    private $bankAccountRepository;

    public function __construct(
        EntityManagerInterface $em,
        ReportRepository $reportRepository
    ) {
        $this->_em = $em;
        $this->reportRepository = $reportRepository;
        $this->casRecRepository = $em->getRepository(CasRec::class);
        $this->assetRepository = $em->getRepository(Asset::class);
        $this->bankAccountRepository = $em->getRepository(BankAccountEntity::class);
    }

    /**
     * Set report type based on CasRec record (if existing).
     *
     * @return string|null type
     *
     * @throws Exception
     */
    public function getReportTypeBasedOnCasrec(Client $client)
    {
        $casRec = $this->casRecRepository->findOneBy(['caseNumber' => $client->getCaseNumber()]);
        if ($casRec instanceof CasRec) {
            $namedDeputy = $client->getNamedDeputy();

            if (!is_null($namedDeputy)) {
                $realm = User::$depTypeIdToRealm[$namedDeputy->getDeputyType()];

                if (!isset($realm)) {
                    throw new \RuntimeException("Named deputy has invalid type {$namedDeputy->getDeputyType()}");
                } else {
                    return CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref(), $realm);
                }
            }

            if (count($client->getUsers())) {
                if ($client->getUsers()->first()->isLayDeputy()) {
                    return CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref(), CasRec::REALM_LAY);
                }
            }

            throw new \RuntimeException('Can\'t determine report realm');
        }

        return null;
    }

    /**
     * Create new year's report copying data over (and set start/endDate accordingly).
     *
     * @return Report
     *
     * @throws Exception
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
            $startDate->modify('+1 day');
        } elseif ($oldReport instanceof Ndr) {
            // when the previous report is NDR we need to work out the new reporting period
            /** @var DateTime $startDate */
            $startDate = $oldReport->getClient()->getExpectedReportStartDate();
            // set default type as oldReport is ndr
            $newReportType = $this->getReportTypeBasedOnCasrec($client) ?: Report::TYPE_102;
        } else {
            throw new \RuntimeException('createNextYearReport() only supports Report and Ndr');
        }

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
     * Clone resources which cross report periods from one account to another.
     *
     * @param Ndr|Report $fromReport
     */
    public function clonePersistentResources(Report $toReport, $fromReport)
    {
        // copy assets
        $toReport->setNoAssetToAdd($fromReport->getNoAssetToAdd());
        $fromAssets = $fromReport->getAssets();
        foreach ($fromAssets as $asset) {
            // Check that the target report doesn't already have a matching asset
            $assetExists = $this->checkAssetExists($toReport, $asset);

            if (!$assetExists) {
                /** @var Asset $newAsset */
                $newAsset = $this->cloneAsset($asset);
                $newAsset->setReport($toReport);

                $toReport->addAsset($newAsset);
                $this->_em->detach($newAsset);
                $this->_em->persist($newAsset);
            }
        }

        // copy bank accounts (opening balance = closing balance, opening date = closing date)
        foreach ($fromReport->getBankAccounts() as $account) {
            // Check that the target report doesn't already have a bank account with that account number
            $accountExists = $this->checkBankAccountExists($toReport, $account);

            if (!$accountExists) {
                $newAccount = $this->cloneBankAccount($account);
                $newAccount->setReport($toReport);
                $toReport->addAccount($newAccount);
                $this->_em->persist($newAccount);
            }
        }
    }

    /**
     * @return bool
     */
    private function checkAssetExists(ReportInterface $toReport, AssetInterface $asset)
    {
        $toAssets = $toReport->getAssets();

        foreach ($toAssets as $toAsset) {
            if ($toAsset->getType() === $asset->getType()) {
                if ($asset->isEqual($toAsset)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    private function checkBankAccountExists(ReportInterface $toReport, BankAccountInterface $account)
    {
        foreach ($toReport->getBankAccounts() as $toAccount) {
            if (
                $toAccount->getAccountType() === $account->getAccountType()
                && $toAccount->getBank() === $account->getBank()
                && $toAccount->getAccountNumber() === $account->getAccountNumber()
                && $toAccount->getSortCode() === $account->getSortCode()
                && !$account->getIsClosed()
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Convert NDR asset into Report Asset.
     *
     * @return ReportAssetOther|NdrAssetOther|ReportAssetProperty
     */
    private function cloneAsset(AssetInterface $asset)
    {
        if (
            $asset instanceof NdrAssetProperty ||
            $asset instanceof ReportAssetProperty
        ) {
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
        } elseif ($asset instanceof NdrAssetOther || $asset instanceof ReportAssetOther) {
            $newAsset = new ReportAssetOther();
            $newAsset->setTitle($asset->getTitle());
            $newAsset->setDescription($asset->getDescription());
            $newAsset->setValuationDate($asset->getValuationDate());
        } else {
            throw new RuntimeException('Unrecognised AssetType');
        }

        $newAsset->setValue($asset->getValue());

        return $newAsset;
    }

    /**
     * Clones instance of ReportInterface and returns new Report Bank Account.
     *
     * @return ReportBankAccount
     */
    private function cloneBankAccount(BankAccountInterface $account)
    {
        $newAccount = new ReportBankAccount();

        $newAccount->setBank($account->getBank());
        $newAccount->setAccountType($account->getAccountType());
        $newAccount->setSortCode($account->getSortCode());
        $newAccount->setAccountNumber($account->getAccountNumber());
        $newAccount->setOpeningBalance($account->getClosingBalance());
        $newAccount->setIsJointAccount($account->getIsJointAccount());
        $newAccount->setCreatedAt(new DateTime());

        return $newAccount;
    }

    /**
     * Set report submitted and create a new year report.
     *
     * @param string|null $ndrDocumentId
     *
     * @return Report
     */
    public function submit(ReportInterface $currentReport, User $user, DateTime $submitDate, $ndrDocumentId = null)
    {
        if (!$currentReport->getAgreedBehalfDeputy()) {
            throw new \RuntimeException('Report must be agreed for submission');
        }

        // update report submit flag, who submitted and date
        $currentReport->setSubmitted(true);
        $currentReport->setSubmittedBy($user);
        $currentReport->setSubmitDate($submitDate);

        // create submission record with NEW documents (= documents not yet attached to a submission)
        $submission = new ReportSubmission($currentReport, $user);
        if ($currentReport instanceof Ndr && (null !== $ndrDocumentId)) {
            $document = $this->_em->getRepository(Document::class)->find($ndrDocumentId);

            if ($document instanceof Document) {
                $document->setReportSubmission($submission);
                $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
                $document->setSynchronisedBy($user);
            }
        } elseif ($currentReport instanceof Report) {
            foreach ($currentReport->getDocuments() as $document) {
                if (!$document->getReportSubmission()) {
                    $document->setReportSubmission($submission);
                    $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
                    $document->setSynchronisedBy($user);
                }
            }
        }

        $this->_em->persist($submission);

        if ($currentReport instanceof Ndr) {
            // Find the first report and clone assets/accounts across
            $reports = $currentReport->getClient()->getReports();

            if (1 === count($reports)) {
                $newYearReport = $reports[0];

                $this->clonePersistentResources($newYearReport, $currentReport);
            } elseif (0 === count($reports)) {
                $newYearReport = $this->createNextYearReport($currentReport);
            }
        } elseif ($currentReport instanceof Report && $currentReport->getUnSubmitDate()) {
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
     * @param mixed $sectionList
     */
    public function unSubmit(Report $report, DateTime $unsubmitDate, DateTime $dueDate, DateTime $startDate, DateTime $endDate, $sectionList)
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
     * Set report submission for additional documents.
     *
     * @return Report new year's report
     */
    public function submitAdditionalDocuments(Report $currentReport, User $user, DateTime $submitDate)
    {
        // create submission record with NEW documents (= documents not yet attached to a submission)
        $submission = new ReportSubmission($currentReport, $user);
        foreach ($currentReport->getDocuments() as $document) {
            if (!$document->getReportSubmission()) {
                $document->setReportSubmission($submission);
                $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
            }
        }

        $this->_em->persist($submission);

        // single transaction flush: current report, submission, new year report
        $this->_em->flush();

        return $currentReport;
    }

    /**
     * If one report started, return the other nonStarted reports with the same start/end date.
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
                'sections' => $ur->getStatus()->getSectionStatus(),
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
                if (
                    $report1->getStatus()->hasStarted()
                    && $report1->hasSamePeriodAs($report2)
                    && !$report2->getStatus()->hasStarted()
                ) {
                    $ret[$report2->getId()] = $report2;
                }
            }
        }

        return $ret;
    }

    /**
     * If the report is ready to submit, but is not yet due, return notFinished instead
     * In all the the cases, return original $status.
     *
     * @param string $status
     *
     * @return string
     */
    public function adjustReportStatus($status, DateTime $endDate)
    {
        if (Report::STATUS_READY_TO_SUBMIT == $status && !self::isDue($endDate)) {
            return Report::STATUS_NOT_FINISHED;
        }

        return $status;
    }

    /**
     * @return bool
     */
    public static function isDue(DateTime $endDate = null)
    {
        if (!$endDate) {
            return false;
        }

        $endOfToday = new DateTime('today midnight');

        return $endDate <= $endOfToday;
    }
}

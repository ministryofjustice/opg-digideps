<?php

namespace App\Service;

use App\Entity\AssetInterface;
use App\Entity\BankAccountInterface;
use App\Entity\Client;
use App\Entity\Ndr\AssetOther as NdrAssetOther;
use App\Entity\Ndr\AssetProperty as NdrAssetProperty;
use App\Entity\Ndr\Ndr;
use App\Entity\PreRegistration;
use App\Entity\Report\Asset;
use App\Entity\Report\AssetOther as ReportAssetOther;
use App\Entity\Report\AssetProperty as ReportAssetProperty;
use App\Entity\Report\BankAccount as ReportBankAccount;
use App\Entity\Report\Document;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\ReportInterface;
use App\Entity\User;
use App\Repository\PreRegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ReportService
{
    private PreRegistrationRepository $preRegistrationRepository;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
        /** @var PreRegistrationRepository $preRegistrationRepository */
        $preRegistrationRepository = $em->getRepository(PreRegistration::class);

        $this->preRegistrationRepository = $preRegistrationRepository;
    }

    /**
     * Set report submitted and create a new year report.
     *
     * @throws \Exception
     */
    public function submit(ReportInterface $currentReport, User $user, \DateTime $submitDate, ?string $ndrDocumentId = null): ?Report
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
            $document = $this->em->getRepository(Document::class)->find($ndrDocumentId);

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

        $this->em->persist($submission);

        $client = $currentReport->getClient();
        $clientId = $client->getId();
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $this->logger->notice("Report submitted for client ID $clientId at $now");

        // Set user to active once they have submitted a report
        $user->setActive(true);

        $newYearReport = null;

        if ($currentReport instanceof Ndr) {
            // Find the first report and clone assets/accounts across
            $reports = $client->getReports();

            if (1 === count($reports)) {
                $this->logger->notice("Populating existing report for client $clientId (NDR submitted, existing report) at $now");

                $newYearReport = $reports[0];

                $this->clonePersistentResources($newYearReport, $currentReport);
            } elseif (0 === count($reports)) {
                $this->logger->notice("Creating next year report for client $clientId (NDR submitted, NO existing report) at $now");

                $newYearReport = $this->createNextYearReport($currentReport);
            }
        } elseif ($currentReport instanceof Report && $currentReport->getUnSubmitDate()) {
            $this->logger->notice("Creating next year report for client $clientId (NO NDR, existing unsubmitted report) at $now");

            // unsubmitted report
            $currentReport->setUnSubmitDate(null);
            $currentReport->setUnsubmittedSectionsList(null);

            // Find the next report and clone assets/accounts across
            $calculatedEndDate = clone $currentReport->getEndDate();
            $calculatedEndDate->modify('+12 months');

            $newYearReport = $client->getReportByEndDate($calculatedEndDate);
            if (!is_null($newYearReport)) {
                $this->clonePersistentResources($newYearReport, $currentReport);
            }
        } else {
            // first-time submission
            $this->logger->notice("Creating next year report for client $clientId (NO NDR, NO existing report) at $now");

            $newYearReport = $this->createNextYearReport($currentReport);
        }

        $this->em->flush(); // single transaction for report.submitted flags + new year report creation

        return $newYearReport;
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
                $this->em->detach($newAsset);
                $this->em->persist($newAsset);
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
                $this->em->persist($newAccount);
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
     * Convert NDR asset into Report Asset.
     *
     * @return ReportAssetOther|NdrAssetOther|ReportAssetProperty
     */
    private function cloneAsset(AssetInterface $asset)
    {
        if (
            $asset instanceof NdrAssetProperty
            || $asset instanceof ReportAssetProperty
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
            throw new \RuntimeException('Unrecognised AssetType');
        }

        $newAsset->setValue($asset->getValue());

        return $newAsset;
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

        return $newAccount;
    }

    /**
     * Create new year's report copying data over (and set start/endDate accordingly).
     *
     * @return Report
     *
     * @throws \Exception
     */
    private function createNextYearReport(ReportInterface $oldReport)
    {
        if (!$oldReport->getSubmitted()) {
            throw new \RuntimeException("Can't create a new year report based on an unsubmitted report");
        }

        $client = $oldReport->getClient();

        if ($oldReport instanceof Report) {
            $startDate = clone $oldReport->getEndDate();
            $newReportType = $oldReport->getType();
            $startDate->modify('+1 day');
        } elseif ($oldReport instanceof Ndr) {
            // when the previous report is NDR we need to work out the new reporting period
            /** @var \DateTime $startDate */
            $startDate = $oldReport->getClient()->getExpectedReportStartDate();
            // set default type as oldReport is ndr
            $newReportType = $this->getReportTypeBasedOnSirius($client) ?: Report::LAY_PFA_HIGH_ASSETS_TYPE;
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
        $this->em->persist($newReport);

        $createdAtStr = 'unknown';
        $createdAt = $newReport->getCreatedAt();
        if (!is_null($createdAt)) {
            $createdAtStr = $createdAt->format('Y-m-d H:i:s');
        }

        $this->logger->notice(
            "Created next year report for client ID {$client->getId()} ".
            "; created at = {$createdAtStr} ".
            "; start date = {$startDate->format('Y-m-d')} ".
            "; end date = {$endDate->format('Y-m-d')}"
        );

        return $newReport;
    }

    /**
     * Set report type based on Sirius record (if existing).
     *
     * @throws \Exception
     */
    public function getReportTypeBasedOnSirius(Client $client): ?string
    {
        // TODO fix dodgy logic - this needs to get the report type based on the specific pre-reg row for the deputy,
        // not just on the first row in the pre-reg table with matching case number
        $preRegistration = $this->preRegistrationRepository->findOneBy(['caseNumber' => $client->getCaseNumber()]);

        if (
            !($preRegistration instanceof PreRegistration)
            || count($client->getUsers()) < 1
            || !$client->getUsers()->first()->isLayDeputy()
        ) {
            return null;
        }

        return PreRegistration::getReportTypeByOrderType(
            $preRegistration->getTypeOfReport(),
            $preRegistration->getOrderType(),
            PreRegistration::REALM_LAY
        );
    }

    public function unSubmit(Report $report, \DateTime $unsubmitDate, \DateTime $dueDate, \DateTime $startDate, \DateTime $endDate, ?string $sectionList)
    {
        // reset report.submitted so that the deputy will set the report back into the dashboard
        $report->setSubmitted(false);

        $report->setUnSubmitDate($unsubmitDate);
        $report->setDueDate($dueDate);
        $report->setStartDate($startDate);
        $report->setEndDate($endDate);

        $report->setUnsubmittedSectionsList($sectionList);

        $this->em->flush();
    }

    /**
     * Set report submission for additional documents.
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
                $document->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);
            }
        }

        if (!$submission->getDocuments()->isEmpty()) {
            $this->em->persist($submission);

            // single transaction flush: current report, submission, new year report
            $this->em->flush();
        }

        return $currentReport;
    }

    /**
     * If the report is ready to submit, but is not yet due, return notFinished instead
     * In all the the cases, return original $status.
     *
     * @param string $status
     *
     * @return string
     */
    public function adjustReportStatus($status, \DateTime $endDate)
    {
        if (Report::STATUS_READY_TO_SUBMIT == $status && !self::isDue($endDate)) {
            return Report::STATUS_NOT_FINISHED;
        }

        return $status;
    }

    /**
     * @return bool
     */
    public static function isDue(?\DateTime $endDate = null)
    {
        if (!$endDate) {
            return false;
        }

        $endOfToday = new \DateTime('today midnight');

        return $endDate < $endOfToday;
    }
}

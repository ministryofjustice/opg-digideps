<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec as CasRecEntity;
use AppBundle\Entity\Report\Asset as AssetEntity;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Repository\CasRecRepository;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Exception\NotFound;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\BankAccount as ReportBankAccount;

class ReportService
{
    /** @var EntityRepository */
    protected $reportRepository;

    /**
     * @var CasRecRepository
     */
    private $casRecRepository;

    public function __construct(
        ReportRepository $reportRepository,
        CasRecRepository $casRecRepository,
        EntityManager $em
    )
    {
        $this->reportRepository = $reportRepository;
        $this->casRecRepository = $casRecRepository;
        $this->_em = $em;
        $this->assetRepository = $em->getRepository(AssetEntity::class);
        $this->bankAccountRepository = $em->getRepository(BankAccountEntity::class);
    }

    public function findById($id)
    {
        return $this->reportRepository->findOneBy(['id' => $id]);
    }

    /**
     * Create new year's report copying data over (and set start/endDate accordingly).
     *
     * @param Report $report
     *
     * @return Report
     */
    public function createNextYearReport(Report $report)
    {
        //lets clone the report
        $newReport = new Report();
        $client = $report->getClient();

        $newReport->setClient($client);

        $casRec = $this->casRecRepository->findOneBy(['caseNumber' => $client->getCaseNumber()]);

        if ($casRec instanceof CasRecEntity) {
            $newReport->setType($report->getTypeBasedOnCasrecRecord($casRec));
        } else {
            throw new NotFound('CasRec record not found');
        }

        $newReport->setStartDate($report->getEndDate()->modify('+1 day'));
        $newReport->setEndDate($report->getEndDate()->modify('+12 months -1 day'));
        $newReport->setReportSeen(false);
        $newReport->setNoAssetToAdd($report->getNoAssetToAdd());

        // clone assets
        foreach ($report->getAssets() as $asset) {
            $newAsset = clone $asset;
            $newAsset->setReport($newReport);
            $this->_em->detach($newAsset);
            $this->_em->persist($newAsset);
        }

        // clone accounts
        //  opening balance = closing balance
        //  opening date = closing date
        foreach ($report->getBankAccounts() as $account) {
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
        // persist
        $this->_em->persist($newReport);
        $this->_em->flush();

        return $newReport;
    }
}

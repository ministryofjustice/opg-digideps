<?php

namespace AppBundle\Service;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\Report\Asset as AssetEntity;
use AppBundle\Entity\Report\BankAccount as BankAccountEntity;
use AppBundle\Entity\Report\BankAccount as ReportBankAccount;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\CasRecRepository;
use AppBundle\Entity\Repository\ReportRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

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
    ) {
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

        if ($casRec instanceof CasRec) {
            $newReport->setType(CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref()));
        } else {
            // @to-do Should we throw an exception here? Use old type for now
            $newReport->setType($report->getType());
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

    /**
     * Using an array of CasRec entities update any corresponding report type if it has been changed
     *
     * @param array $casRecEntities
     *
     * @throws \Exception
     */
    public function updateCurrentReportTypes(array $casRecEntities)
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

        $reports = $qb->getQuery()->getResult(); /* @var $reports Report[] */

        //  Loop through the reports and update the report type if necessary
        foreach ($reports as $report) {
            $reportClientCaseNumber = $report->getClient()->getCaseNumber();

            if (isset($casRecEntitiesWithKey[$reportClientCaseNumber])) {
                //  Get the report type based on the CasRec record
                $casRec = $casRecEntitiesWithKey[$reportClientCaseNumber];
                $casRecReportType = CasRec::getTypeBasedOnTypeofRepAndCorref($casRec->getTypeOfReport(), $casRec->getCorref());

                if ($report->getType() != $casRecReportType) {
                    $report->setType($casRecReportType);
                    $this->_em->persist($report);
                }
            }
        }

        $this->_em->flush();
    }
}

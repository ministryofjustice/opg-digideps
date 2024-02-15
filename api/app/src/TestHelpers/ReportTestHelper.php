<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Ndr\BankAccount as NdrBankAccount;
use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\Debt as NdrDebt;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf as NdrMoneyReceivedOnClientsBehalf;
use App\Entity\Ndr\Ndr;
use App\Entity\Ndr\VisitsCare as NdrVisitsCare;
use App\Entity\Report\Action;
use App\Entity\Report\BankAccount;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Debt as ReportDebt;
use App\Entity\Report\Document;
use App\Entity\Report\Lifestyle;
use App\Entity\Report\MentalCapacity;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\ProfDeputyOtherCost;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\Report\VisitsCare;
use App\Entity\ReportInterface;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class ReportTestHelper
{
    /**
     * @return Report
     */
    public function generateReport(EntityManager $em, Client $client = null, string $type = null, \DateTime $startDate = null, \DateTime $endDate = null)
    {
        $client = $client ? $client : (new ClientTestHelper())->generateClient($em);
        $type = $type ? $type : Report::LAY_PFA_HIGH_ASSETS_TYPE;
        $startDate = $startDate ? $startDate : new \DateTime('2 years ago');
        $endDate = $endDate ? $endDate : (clone $startDate)->add(new \DateInterval('P1Y'));

        $report = new Report($client, $type, $startDate, $endDate);
        $this->completeBankAccounts($report, $em);

        return $report;
    }

    public function generateNdr(EntityManager $em, User $deputy, Client $client = null): Ndr
    {
        $ndr = new Ndr($client);
        $deputy->setNdrEnabled(true);
        $client->setNdr($ndr);

        $deputy->addClient($client);

        $this->completeBankAccounts($ndr, $em);

        return $ndr;
    }

    public function completeLayReport(ReportInterface $report, EntityManager $em): void
    {
        $this->completeDecisions($report);
        $this->completeContacts($report);
        $this->completeVisitsCare($report);
        $this->completeActions($report);
        $this->completeOtherInfo($report);
        $this->completeDocuments($report, $em);
        $this->completeDeputyExpenses($report);
        $this->completeGifts($report);
        $this->completeMoneyIn($report);
        $this->completeMoneyOut($report);
        $this->completeMoneyInShort($report);
        $this->completeMoneyOutShort($report);
        $this->completeAssets($report);
        $this->completeDebts($report, $em);
        $this->completeLifestyle($report);
        $this->completeClientBenefitsCheck($report);
    }

    public function completeNdrLayReport(ReportInterface $report, EntityManager $em): void
    {
        $this->completeVisitsCare($report);
        $this->completeActions($report);
        $this->completeOtherInfo($report);
        $this->completeDeputyExpenses($report);
        $this->completeIncomeBenefits($report);
        $this->completeAssets($report);
        $this->completeDebts($report, $em);
        $this->completeClientBenefitsCheck($report);
    }

    public function submitReport(ReportInterface $report, EntityManager $em): void
    {
        if ($report->getClient()->getOrganisation()) {
            $submittedBy = $report->getClient()->getOrganisation()->getUsers()[0];
        } else {
            $submittedBy = $report->getClient()->getUsers()->first();
        }

        $submitDate = clone $report->getStartDate();
        $submitDate->modify('+365 day');

        $reportPdf = new Document($report);
        $reportPdf->setFileName('DigiRep-2020-2021-12-34_12345678.pdf');
        $reportPdf->setStorageReference('dd_doc_1234_9876543219876');
        $reportPdf->setIsReportPdf(true);
        $reportPdf->setCreatedOn(new \DateTime());
        $reportPdf->setCreatedBy($submittedBy);
        $reportPdf->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

        $submission = (new ReportSubmission($report, $submittedBy))
            ->setCreatedBy($submittedBy)
            ->setCreatedOn($submitDate)
            ->addDocument($reportPdf);

        if (!($report instanceof Ndr\Ndr)) {
            $supportingDocument = new Document($report);
            $supportingDocument->setFileName('fake-file.pdf');
            $supportingDocument->setStorageReference('dd_doc_1234_123456789123456');
            $supportingDocument->setIsReportPdf(false);
            $supportingDocument->setCreatedOn(new \DateTime());
            $supportingDocument->setCreatedBy($submittedBy);
            $supportingDocument->setSynchronisationStatus(Document::SYNC_STATUS_QUEUED);

            $submission->addDocument($supportingDocument);
            $supportingDocument->setReportSubmission($submission);

            $em->persist($supportingDocument);
        }

        $reportPdf->setReportSubmission($submission);

        $report
            ->setSubmitDate($submitDate)
            ->setSubmitted(true)
            ->setSubmittedBy($submittedBy)
            ->setWishToProvideDocumentation('yes');

        $em->persist($reportPdf);
        $em->persist($submission);
        $em->persist($report);

        // Create next report
        $newReportStartDate = clone $report->getEndDate();
        $newReportStartDate->modify('+1 day');
        $newReportEndDate = clone $newReportStartDate;
        $newReportEndDate->modify('+365 day');

        $client = $report->getClient();
        $newReport = $this->generateReport($em, $client, $report->getType(), $newReportStartDate, $newReportEndDate);

        $client->addReport($newReport);
        $newReport->setClient($client);

        $em->persist($client);
        $em->persist($newReport);
    }

    /**
     * @throws \Exception
     */
    private function completeDecisions(ReportInterface $report): void
    {
        $report->setReasonForNoDecisions('No need for decisions');
        (new MentalCapacity($report))->setHasCapacityChanged('no')->setMentalAssessmentDate(new \DateTime());
    }

    private function completeContacts(ReportInterface $report): void
    {
        $report->setReasonForNoContacts('No need for contacts');
    }

    private function completeVisitsCare(ReportInterface $report): void
    {
        if ($report instanceof Ndr) {
            $vc = (new NdrVisitsCare())
                ->setNdr($report)
                ->setPlanMoveNewResidence('no');
        } else {
            $vc = (new VisitsCare())->setReport($report);
        }

        $vc
            ->setDoYouLiveWithClient('yes')
            ->setDoesClientReceivePaidCare('no')
            ->setWhoIsDoingTheCaring('me')
            ->setDoesClientHaveACarePlan('no');

        $report->setVisitsCare($vc);
    }

    private function completeActions(ReportInterface $report): void
    {
        if ($report instanceof Ndr) {
            $report
                ->setActionGiveGiftsToClient('no')
                ->setActionPropertyMaintenance('no')
                ->setActionPropertySellingRent('no')
                ->setActionPropertyBuy('no');
        } else {
            $action = (new Action($report))
                ->setDoYouExpectFinancialDecisions('no')
                ->setDoYouHaveConcerns('no');
            $report->setAction($action);
        }
    }

    private function completeOtherInfo(ReportInterface $report): void
    {
        $report->setActionMoreInfo('no');
    }

    private function completeLifestyle(ReportInterface $report): void
    {
        $ls = (new Lifestyle())
            ->setReport($report);
        $ls->setCareAppointments('no');
        $ls->setDoesClientUndertakeSocialActivities('no');
        $report->setLifestyle($ls);
    }

    private function completeDocuments(ReportInterface $report, EntityManager $em): void
    {
        $report->setWishToProvideDocumentation('no');
    }

    private function completeGifts(ReportInterface $report): void
    {
        $report->setGiftsExist('no');
    }

    private function completeBankAccounts(ReportInterface $report, EntityManager $em): void
    {
        if ($report instanceof Ndr) {
            $ba = (new NdrBankAccount())
                ->setNdr($report)
                ->setAccountNumber('1234');

            $report->addAccount($ba);
            $em->persist($ba);
        } else {
            $ba = (new BankAccount())
                ->setReport($report)
                ->setClosingBalance(1000)
                ->setAccountNumber('1234');

            $report->addAccount($ba);
            $report->setBalanceMismatchExplanation('no reason');
        }
    }

    private function completeMoneyIn(ReportInterface $report): void
    {
        if (Report::LAY_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_HIGH_ASSETS_TYPE === $report->getType()) {
            $report->setMoneyInExists('Yes');
        }
        
        $mt = (new MoneyTransaction($report))->setCategory('salary-or-wages')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    private function completeMoneyOut(ReportInterface $report): void
    {
        if (Report::LAY_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_HIGH_ASSETS_TYPE === $report->getType()) {
            $report->setMoneyOutExists('Yes');
        }
        
        $mt = (new MoneyTransaction($report))->setCategory('care-fees')->setAmount(200);
        $report->addMoneyTransaction($mt);
        $mt2 = (new MoneyTransaction($report))->setCategory('electricity')->setAmount(100);
        $report->addMoneyTransaction($mt2);
    }

    private function completeAssets(ReportInterface $report): void
    {
        $report->setNoAssetToAdd(true);
    }

    private function completeDebts(ReportInterface $report, EntityManager $em): void
    {
        $report->setHasDebts('yes');

        if ($report instanceof Ndr) {
            $debt = new NdrDebt(
                $report,
                'care-fees',
                false,
                10.0
            );
        } else {
            $debt = new ReportDebt(
                $report,
                'care-fees',
                false,
                10.0
            );
        }

        $report->setDebtManagement('Slowly paying it off');
        $report->addDebt($debt);

        $em->persist($debt);
        $em->persist($report);
    }

    private function completeMoneyInShort(ReportInterface $report): void
    {
        if (Report::LAY_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_LOW_ASSETS_TYPE === $report->getType()) 
        {
            $report->setMoneyInExists('No');
            $report->setReasonForNoMoneyIn('No money in');
        }
    }

    private function completeMoneyOutShort(ReportInterface $report): void
    {
        if (Report::LAY_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_LOW_ASSETS_TYPE === $report->getType())
        {
            $report->setMoneyOutExists('No');
            $report->setReasonForNoMoneyOut('No money out');
        }
    }

    private function completeDeputyExpenses(ReportInterface $report): void
    {
        if ($report instanceof Ndr || $report->isLayReport()) {
            $report->setPaidForAnything('no');
        } elseif ($report->isPAreport()) {
            $report->setReasonForNoFees('No reason for no fees');
            $report->setPaidForAnything('no');
        } else {
            $report->setCurrentProfPaymentsReceived('no');
            $report
                ->setProfDeputyCostsHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED)
                ->setProfDeputyCostsHasPrevious('no')
                ->setProfDeputyFixedCost(1000)
                ->setProfDeputyOtherCosts(new ArrayCollection())
                ->addProfDeputyOtherCost(new ProfDeputyOtherCost($report, 1, false, 500));

            $report->setProfDeputyCostsEstimateHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED);
        }
    }

    private function completeExpenses(ReportInterface $report): void
    {
        $this->completeDeputyExpenses($report);
    }

    private function completeIncomeBenefits(ReportInterface $report)
    {
        if (!$report instanceof Ndr) {
            return;
        }

        $report
            ->setReceiveStatePension('no')
            ->setReceiveOtherIncome('no')
            ->setExpectCompensationDamages('no');
    }

    private function completeMoneyTransfers(ReportInterface $report)
    {
        if (!$report instanceof Ndr) {
            return;
        }

        $report->setNoTransfersToAdd(true);
    }

    private function completeBalance(ReportInterface $report)
    {
        if (!$report instanceof Ndr) {
            return;
        }

        $report->setBalanceMismatchExplanation('no reason');
    }

    private function completeProfDeputyCosts(ReportInterface $report)
    {
        $this->completeDeputyExpenses($report);
    }

    private function completeProfDeputyCostsEstimate(ReportInterface $report)
    {
        if ($report->isProfReport()) {
            $report->setProfDeputyCostsEstimateHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED);
        }
    }

    private function completePaFeeExpense(ReportInterface $report)
    {
        $this->completeDeputyExpenses($report);
    }

    private function completeClientBenefitsCheck(ReportInterface $report): void
    {
        $typeOfIncome = $report instanceof Ndr ? new NdrMoneyReceivedOnClientsBehalf() : new MoneyReceivedOnClientsBehalf();
        $clientBenefitsCheck = $report instanceof Ndr ? new NdrClientBenefitsCheck() : new ClientBenefitsCheck();

        $typeOfIncome->setCreated(new \DateTime())
            ->setAmount(100.50)
            ->setWhoReceivedMoney('Some other bloke')
            ->setMoneyType('Universal Credit');

        $clientBenefitsCheck->setReport($report)
            ->setWhenLastCheckedEntitlement(ClientBenefitsCheck::WHEN_CHECKED_I_HAVE_CHECKED)
            ->setDateLastCheckedEntitlement(new \DateTime())
            ->setCreated(new \DateTime())
            ->setDoOthersReceiveMoneyOnClientsBehalf('yes')
            ->addTypeOfMoneyReceivedOnClientsBehalf($typeOfIncome)
        ;

        $typeOfIncome->setClientBenefitsCheck($clientBenefitsCheck);

        $report->setClientBenefitsCheck($clientBenefitsCheck);
    }
}

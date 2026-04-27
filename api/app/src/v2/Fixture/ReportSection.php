<?php

namespace App\v2\Fixture;

use App\Entity\Report\Action;
use App\Entity\Report\BankAccount;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Lifestyle;
use App\Entity\Report\MentalCapacity;
use App\Entity\Report\MoneyReceivedOnClientsBehalf;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\ProfDeputyOtherCost;
use App\Entity\Report\Report;
use App\Entity\Report\VisitsCare;
use Doctrine\Common\Collections\ArrayCollection;

class ReportSection
{
    public function __construct()
    {
    }

    public function completeReport(Report $report): void
    {
        $this->completeDecisions($report);
        $this->completeContacts($report);
        $this->completeVisitsCare($report);
        $this->completeActions($report);
        $this->completeOtherInfo($report);
        $this->completeDocuments($report);
        $this->completeDeputyExpenses($report);
        $this->completeGifts($report);
        $this->completeBankAccounts($report);
        $this->completeMoneyIn($report);
        $this->completeMoneyOut($report);
        $this->completeMoneyInShort($report);
        $this->completeMoneyOutShort($report);
        $this->completeAssets($report);
        $this->completeDebts($report);
        $this->completeLifestyle($report);
        $this->completeClientBenefitsCheck($report);
    }

    public function completeSection(Report $report, string $section): void
    {
        // Convert visits_care to VisitsCare, for example.
        $section = str_replace('_', ' ', $section);
        $section = str_replace(' ', '', ucwords($section));

        $method = sprintf('complete%s', $section);
        $this->{$method}($report);
    }

    /**
     * @throws \Exception
     */
    private function completeDecisions(Report $report): void
    {
        $report->setSignificantDecisionsMade('No');
        $report->setReasonForNoDecisions('No need for decisions');
        (new MentalCapacity($report))->setHasCapacityChanged('no')->setMentalAssessmentDate(new \DateTime());
    }

    private function completeContacts(Report $report): void
    {
        $report->setReasonForNoContacts('No need for contacts');
    }

    private function completeVisitsCare(Report $report): void
    {
        $vc = (new VisitsCare())
            ->setReport($report)
            ->setDoYouLiveWithClient('yes')
            ->setDoesClientReceivePaidCare('no')
            ->setWhoIsDoingTheCaring('me')
            ->setDoesClientHaveACarePlan('no');

        $report->setVisitsCare($vc);
    }

    private function completeActions(Report $report): void
    {
        $action = (new Action($report))
            ->setDoYouExpectFinancialDecisions('no')
            ->setDoYouHaveConcerns('no');
        $report->setAction($action);
    }

    private function completeOtherInfo(Report $report): void
    {
        $report->setActionMoreInfo('no');
    }

    private function completeLifestyle(Report $report): void
    {
        $ls = (new Lifestyle())
            ->setReport($report);
        $ls->setCareAppointments('no');
        $ls->setDoesClientUndertakeSocialActivities('no');
        $report->setLifestyle($ls);
    }

    private function completeDocuments(Report $report): void
    {
        $report->setWishToProvideDocumentation('no');
    }

    private function completeGifts(Report $report): void
    {
        $report->setGiftsExist('no');
    }

    private function completeBankAccounts(Report $report): void
    {
        $ba = (new BankAccount())->setReport($report)->setClosingBalance(1000);
        $report->addAccount($ba);
        $report->setBalanceMismatchExplanation('no reason');
    }

    private function completeMoneyIn(Report $report): void
    {
        if (
            Report::LAY_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
        ) {
            $report->setMoneyInExists('Yes');
        }
        $mt = (new MoneyTransaction($report))->setCategory('salary-or-wages')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    private function completeMoneyOut(Report $report): void
    {
        if (
            Report::LAY_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_HIGH_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_HIGH_ASSETS_TYPE === $report->getType()
        ) {
            $report->setMoneyOutExists('Yes');
        }
        $mt = (new MoneyTransaction($report))->setCategory('care-fees')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    private function completeAssets(Report $report): void
    {
        $report->setNoAssetToAdd(true);
    }

    private function completeDebts(Report $report): void
    {
        $report->setHasDebts('no');
    }

    private function completeMoneyInShort(Report $report): void
    {
        if (
            Report::LAY_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_LOW_ASSETS_TYPE === $report->getType()
        ) {
            $report->setMoneyInExists('No');
            $report->setReasonForNoMoneyIn('No money in');
        }
        $report->setMoneyTransactionsShortInExist('no');
    }

    private function completeMoneyOutShort(Report $report): void
    {
        if (
            Report::LAY_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_LOW_ASSETS_TYPE === $report->getType()
        ) {
            $report->setMoneyOutExists('No');
            $report->setReasonForNoMoneyOut('No money out');
        }
        $report->setMoneyTransactionsShortOutExist('no');
    }

    private function completeDeputyExpenses(Report $report): void
    {
        if ($report->isLayReport()) {
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

    private function completeExpenses(Report $report): void
    {
        $this->completeDeputyExpenses($report);
    }

    private function completeProfDeputyCosts(Report $report)
    {
        $this->completeDeputyExpenses($report);
    }

    private function completeProfDeputyCostsEstimate(Report $report)
    {
        if ($report->isProfReport()) {
            $report->setProfDeputyCostsEstimateHowCharged(Report::PROF_DEPUTY_COSTS_TYPE_FIXED);
        }
    }

    private function completePaFeeExpense(Report $report)
    {
        $this->completeDeputyExpenses($report);
    }

    private function completeClientBenefitsCheck(Report $report): void
    {
        $typeOfIncome = new MoneyReceivedOnClientsBehalf();

        $typeOfIncome->setCreated(new \DateTime())
            ->setWhoReceivedMoney('Someone')
            ->setAmount(100.50)
            ->setMoneyType('Universal Credit');

        $clientBenefitsCheck = new ClientBenefitsCheck();

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

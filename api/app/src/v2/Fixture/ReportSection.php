<?php

namespace App\v2\Fixture;

use App\Entity\Ndr;
use App\Entity\Ndr\ClientBenefitsCheck as NdrClientBenefitsCheck;
use App\Entity\Ndr\MoneyReceivedOnClientsBehalf as NdrMoneyReceivedOnClientsBehalf;
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
use App\Entity\ReportInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ReportSection
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function completeReport(ReportInterface $report): void
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

    public function completeSection(ReportInterface $report, string $section): void
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
    private function completeDecisions(ReportInterface $report): void
    {
        $report->setSignificantDecisionsMade('No');
        $report->setReasonForNoDecisions('No need for decisions');
        (new MentalCapacity($report))->setHasCapacityChanged('no')->setMentalAssessmentDate(new \DateTime());
    }

    private function completeContacts(ReportInterface $report): void
    {
        $report->setReasonForNoContacts('No need for contacts');
    }

    private function completeVisitsCare(ReportInterface $report): void
    {
        if ($report instanceof Ndr\Ndr) {
            $vc = (new Ndr\VisitsCare())
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
        if ($report instanceof Ndr\Ndr) {
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

    private function completeDocuments(ReportInterface $report): void
    {
        $report->setWishToProvideDocumentation('no');
    }

    private function completeGifts(ReportInterface $report): void
    {
        $report->setGiftsExist('no');
    }

    private function completeBankAccounts(ReportInterface $report): void
    {
        if ($report instanceof Ndr\Ndr) {
            $ba = (new Ndr\BankAccount())->setNdr($report);
            $this->em->persist($ba);
        } else {
            $ba = (new BankAccount())->setReport($report)->setClosingBalance(1000);
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
    }

    private function completeAssets(ReportInterface $report): void
    {
        $report->setNoAssetToAdd(true);
    }

    private function completeDebts(ReportInterface $report): void
    {
        $report->setHasDebts('no');
    }

    private function completeMoneyInShort(ReportInterface $report): void
    {
        if (Report::LAY_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_LOW_ASSETS_TYPE === $report->getType()) {
            $report->setMoneyInExists('No');
            $report->setReasonForNoMoneyIn('No money in');
        }
        $report->setMoneyTransactionsShortInExist('no');
    }

    private function completeMoneyOutShort(ReportInterface $report): void
    {
        if (Report::LAY_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::LAY_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PA_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PA_COMBINED_LOW_ASSETS_TYPE === $report->getType()
            || Report::PROF_PFA_LOW_ASSETS_TYPE === $report->getType() || Report::PROF_COMBINED_LOW_ASSETS_TYPE === $report->getType()) {
            $report->setMoneyOutExists('No');
            $report->setReasonForNoMoneyOut('No money out');
        }
        $report->setMoneyTransactionsShortOutExist('no');
    }

    private function completeDeputyExpenses(ReportInterface $report): void
    {
        if ($report instanceof Ndr\Ndr || $report->isLayReport()) {
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
        if (!$report instanceof Ndr\Ndr) {
            return;
        }

        $report
            ->setReceiveStatePension('no')
            ->setReceiveOtherIncome('no')
            ->setExpectCompensationDamages('no');
    }

    private function completeMoneyTransfers(ReportInterface $report)
    {
        if (!$report instanceof Ndr\Ndr) {
            return;
        }

        $report->setNoTransfersToAdd(true);
    }

    private function completeBalance(ReportInterface $report)
    {
        if (!$report instanceof Ndr\Ndr) {
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
        $typeOfIncome = $report instanceof Ndr\Ndr ? new NdrMoneyReceivedOnClientsBehalf() : new MoneyReceivedOnClientsBehalf();

        $typeOfIncome->setCreated(new \DateTime())
            ->setWhoReceivedMoney('Someone')
            ->setAmount(100.50)
            ->setMoneyType('Universal Credit');

        $clientBenefitsCheck = $report instanceof Ndr\Ndr ? new NdrClientBenefitsCheck() : new ClientBenefitsCheck();

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

<?php

namespace AppBundle\v2\Fixture;

use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Lifestyle;
use AppBundle\Entity\Report\MentalCapacity;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\VisitsCare;
use Doctrine\Common\Collections\ArrayCollection;

class ReportSection
{
    public function completeReport(Report $report): void
    {
        $this->completeDecisions($report);
        $this->completeContacts($report);
        $this->completeVisitsCare($report);
        $this->completeActions($report);
        $this->completeOtherInfo($report);
        $this->completeDocuments($report);
        $this->completeExpenses($report);
        $this->completeGifts($report);
        $this->completeBankAccounts($report);
        $this->completeMoneyIn($report);
        $this->completeMoneyOut($report);
        $this->completeMoneyInShort($report);
        $this->completeMoneyOutShort($report);
        $this->completeAssets($report);
        $this->completeDebts($report);
        $this->completeLifestyle($report);
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
     * @param Report $report
     * @throws \Exception
     */
    private function completeDecisions(Report $report): void
    {
        $report->setReasonForNoDecisions('No need for decisions');
        (new MentalCapacity($report))->setHasCapacityChanged('no')->setMentalAssessmentDate(new \DateTime());
    }

    /**
     * @param Report $report
     */
    private function completeContacts(Report $report): void
    {
        $report->setReasonForNoContacts('No need for contacts');
    }

    /**
     * @param Report $report
     */
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

    /**
     * @param Report $report
     */
    private function completeActions(Report $report): void
    {
        $action = (new Action($report))
            ->setDoYouExpectFinancialDecisions('no')
            ->setDoYouHaveConcerns('no');
        $report->setAction($action);
    }

    /**
     * @param Report $report
     */
    private function completeOtherInfo(Report $report): void
    {
        $report->setActionMoreInfo('no');
    }

    /**
     * @param Report $report
     */
    private function completeLifestyle(Report $report): void
    {
        $ls = (new Lifestyle())
            ->setReport($report);
        $ls->setCareAppointments('no');
        $ls->setDoesClientUndertakeSocialActivities('no');
        $report->setLifestyle($ls);
    }

    /**
     * @param Report $report
     */
    private function completeDocuments(Report $report): void
    {
        $report->setWishToProvideDocumentation('no');
    }

    /**
     * @param Report $report
     */
    private function completeGifts(Report $report): void
    {
        $report->setGiftsExist('no');
    }

    /**
     * @param Report $report
     */
    private function completeBankAccounts(Report $report): void
    {
        $ba = (new BankAccount())->setReport($report)->setClosingBalance(1000);
        $report->addAccount($ba);
        $report->setBalanceMismatchExplanation('no reason');
    }

    /**
     * @param Report $report
     */
    private function completeMoneyIn(Report $report): void
    {
        $mt = (new MoneyTransaction($report))->setCategory('salary-or-wages')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    /**
     * @param Report $report
     */
    private function completeMoneyOut(Report $report): void
    {
        $mt = (new MoneyTransaction($report))->setCategory('care-fees')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    /**
     * @param Report $report
     */
    private function completeAssets(Report $report): void
    {
        $report->setNoAssetToAdd(true);
    }

    /**
     * @param Report $report
     */
    private function completeDebts(Report $report): void
    {
        $report->setHasDebts('no');
    }

    /**
     * @param Report $report
     */
    private function completeMoneyInShort(Report $report): void
    {
        $report->setMoneyTransactionsShortInExist('no');
    }

    /**
     * @param Report $report
     */
    private function completeMoneyOutShort(Report $report): void
    {
        $report->setMoneyTransactionsShortOutExist('no');
    }

    /**
     * @param Report $report
     */
    private function completeExpenses(Report $report): void
    {
        if ($report->isLayReport()) {
            $report->setPaidForAnything('no');
        } else if ($report->isPAreport()) {
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
}

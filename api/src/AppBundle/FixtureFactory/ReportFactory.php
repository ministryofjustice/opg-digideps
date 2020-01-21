<?php

namespace AppBundle\FixtureFactory;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Lifestyle;
use AppBundle\Entity\Report\MentalCapacity;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\VisitsCare;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;

class ReportFactory
{
    /**
     * @param array $data
     * @param Client $client
     * @return Report
     * @throws \Exception
     */
    public function create(array $data, Client $client): Report
    {
        if ($data['deputyType'] === User::TYPE_LAY) {
            $type = $data['reportType'];
        } else if ($data['deputyType'] === User::TYPE_PA) {
            $type = $data['reportType'] . '-6';
        } else if ($data['deputyType'] === User::TYPE_PROF) {
            $type = $data['reportType'] . '-5';
        }

        $startDate = $client->getExpectedReportStartDate($client->getCourtDate()->format('Y'));
        $endDate = $client->getExpectedReportEndDate($client->getCourtDate()->format('Y'));

        $report = new Report($client, $type, $startDate, $endDate);

        if (isset($data['reportStatus']) && $data['reportStatus'] === Report::STATUS_READY_TO_SUBMIT) {
            $this->completeDecisions($report);
            $this->completeContacts($report);
            $this->completeVistsAndCare($report);
            $this->completeActions($report);
            $this->completeOtherInfo($report);
            $this->completeDocumentation($report);
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
            $report->updateSectionsStatusCache($report->getAvailableSections());
        }

        return $report;
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
    private function completeVistsAndCare(Report $report): void
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
    private function completeDocumentation(Report $report): void
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

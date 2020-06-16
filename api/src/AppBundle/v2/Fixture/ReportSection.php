<?php

namespace AppBundle\v2\Fixture;

use AppBundle\Entity\Ndr as Ndr;
use AppBundle\Entity\Report\Action;
use AppBundle\Entity\Report\BankAccount;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Lifestyle;
use AppBundle\Entity\Report\MentalCapacity;
use AppBundle\Entity\Report\MoneyTransaction;
use AppBundle\Entity\Report\ProfDeputyOtherCost;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\VisitsCare;
use AppBundle\Entity\ReportInterface;
use AppBundle\Service\File\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\TestHelpers\DocumentHelpers;

class ReportSection
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
    }

    /**
     * @param ReportInterface $report
     * @param string $section
     */
    public function completeSection(ReportInterface $report, string $section): void
    {
        // Convert visits_care to VisitsCare, for example.
        $section = str_replace('_', ' ', $section);
        $section = str_replace(' ', '', ucwords($section));

        $method = sprintf('complete%s', $section);
        $this->{$method}($report);
    }

    /**
     * @param ReportInterface $report
     * @throws \Exception
     */
    private function completeDecisions(ReportInterface $report): void
    {
        $report->setReasonForNoDecisions('No need for decisions');
        (new MentalCapacity($report))->setHasCapacityChanged('no')->setMentalAssessmentDate(new \DateTime());
    }

    /**
     * @param ReportInterface $report
     */
    private function completeContacts(ReportInterface $report): void
    {
        $report->setReasonForNoContacts('No need for contacts');
    }

    /**
     * @param ReportInterface $report
     */
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

    /**
     * @param ReportInterface $report
     */
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

    /**
     * @param ReportInterface $report
     */
    private function completeOtherInfo(ReportInterface $report): void
    {
        $report->setActionMoreInfo('no');
    }

    /**
     * @param ReportInterface $report
     */
    private function completeLifestyle(ReportInterface $report): void
    {
        $ls = (new Lifestyle())
            ->setReport($report);
        $ls->setCareAppointments('no');
        $ls->setDoesClientUndertakeSocialActivities('no');
        $report->setLifestyle($ls);
    }

    /**
     * @param ReportInterface $report
     */
    private function completeDocuments(ReportInterface $report): void
    {
        $report->setWishToProvideDocumentation('no');
    }

    /**
     * @param ReportInterface $report
     */
    private function completeGifts(ReportInterface $report): void
    {
        $report->setGiftsExist('no');
    }

    /**
     * @param ReportInterface $report
     */
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

    /**
     * @param ReportInterface $report
     */
    private function completeMoneyIn(ReportInterface $report): void
    {
        $mt = (new MoneyTransaction($report))->setCategory('salary-or-wages')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    /**
     * @param ReportInterface $report
     */
    private function completeMoneyOut(ReportInterface $report): void
    {
        $mt = (new MoneyTransaction($report))->setCategory('care-fees')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    /**
     * @param ReportInterface $report
     */
    private function completeAssets(ReportInterface $report): void
    {
        $report->setNoAssetToAdd(true);
    }

    /**
     * @param ReportInterface $report
     */
    private function completeDebts(ReportInterface $report): void
    {
        $report->setHasDebts('no');
    }

    /**
     * @param ReportInterface $report
     */
    private function completeMoneyInShort(ReportInterface $report): void
    {
        $report->setMoneyTransactionsShortInExist('no');
    }

    /**
     * @param ReportInterface $report
     */
    private function completeMoneyOutShort(ReportInterface $report): void
    {
        $report->setMoneyTransactionsShortOutExist('no');
    }

    /**
     * @param ReportInterface $report
     */
    private function completeDeputyExpenses(ReportInterface $report): void
    {
        if ($report instanceof Ndr\Ndr || $report->isLayReport()) {
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

    /**
     * @param ReportInterface $report
     */
    private function completeExpenses(ReportInterface $report): void
    {
        $this->completeDeputyExpenses($report);
    }

    /**
     * @param ReportInterface $report
     */
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

    /**
     * @param ReportInterface $report
     */
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

}

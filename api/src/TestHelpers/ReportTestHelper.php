<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Ndr\Debt as NdrDebt;
use App\Entity\Ndr\Ndr;
use App\Entity\Report\Action;
use App\Entity\Report\BankAccount;
use App\Entity\Report\Debt as ReportDebt;
use App\Entity\Report\Lifestyle;
use App\Entity\Report\MentalCapacity;
use App\Entity\Report\MoneyTransaction;
use App\Entity\Report\ProfDeputyOtherCost;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Entity\Report\VisitsCare;
use App\Entity\ReportInterface;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

class ReportTestHelper
{
    /**
     * @return Report
     */
    public function generateReport(EntityManager $em, ?Client $client = null, ?string $type = null, ?DateTime $startDate = null, ?DateTime $endDate = null)
    {
        $client = $client ? $client : (new ClientTestHelper())->generateClient($em);
        $type = $type ? $type : Report::TYPE_102;
        $startDate = $startDate ? $startDate : new \DateTime('2 years ago');
        $endDate = $endDate ? $endDate : (clone $startDate)->add(new DateInterval('P1Y'));

        return new Report($client, $type, $startDate, $endDate);
    }

    public function completeLayReport(ReportInterface $report, EntityManager $em): void
    {
        $this->completeDecisions($report);
        $this->completeContacts($report);
        $this->completeVisitsCare($report);
        $this->completeActions($report);
        $this->completeOtherInfo($report);
        $this->completeDocuments($report);
        $this->completeDeputyExpenses($report);
        $this->completeGifts($report);
        $this->completeBankAccounts($report, $em);
        $this->completeMoneyIn($report);
        $this->completeMoneyOut($report);
        $this->completeMoneyInShort($report);
        $this->completeMoneyOutShort($report);
        $this->completeAssets($report);
        $this->completeDebts($report, $em);
        $this->completeLifestyle($report);
    }

    public function completeNdrLayReport(ReportInterface $report, EntityManager $em): void
    {
        $this->completeVisitsCare($report);
        $this->completeActions($report);
        $this->completeOtherInfo($report);
        $this->completeDeputyExpenses($report);
        $this->completeIncomeBenefits($report);
        $this->completeBankAccounts($report, $em);
        $this->completeAssets($report);
        $this->completeDebts($report);
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

        $submission = (new ReportSubmission($report, $submittedBy))
            ->setCreatedBy($submittedBy)
            ->setCreatedOn($submitDate);

        $report
            ->setSubmitDate($submitDate)
            ->setSubmitted(true);

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

    private function completeBankAccounts(ReportInterface $report, EntityManager $em): void
    {
        if ($report instanceof Ndr\Ndr) {
            $ba = (new Ndr\BankAccount())->setNdr($report);
            $report->addAccount($ba);
            $em->persist($ba);
        } else {
            $ba = (new BankAccount())->setReport($report)->setClosingBalance(1000);
            $report->addAccount($ba);
            $report->setBalanceMismatchExplanation('no reason');
        }
    }

    private function completeMoneyIn(ReportInterface $report): void
    {
        $mt = (new MoneyTransaction($report))->setCategory('salary-or-wages')->setAmount(200);
        $report->addMoneyTransaction($mt);
    }

    private function completeMoneyOut(ReportInterface $report): void
    {
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
        $report->setMoneyTransactionsShortInExist('no');
    }

    private function completeMoneyOutShort(ReportInterface $report): void
    {
        $report->setMoneyTransactionsShortOutExist('no');
//        $report->setMoneyTransactionsShort(new ArrayCollection([
//            (new MoneyTransactionShortOut($report))->setAmount(1001)
//        ]));
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
}

<?php

namespace App\EventListener;

use App\Entity\Report\Asset;
use App\Entity\Report\Contact;
use App\Entity\Report\Decision;
use App\Entity\Report\Document;
use App\Entity\Report\Expense;
use App\Entity\Report\Gift;
use App\Entity\Report\MoneyTransactionShortIn;
use App\Entity\Report\MoneyTransactionShortOut;
use App\Entity\Report\MoneyTransfer;
use App\Entity\Report\ProfServiceFeeCurrent;
use App\Entity\Report\Report;
use App\Entity\Report\ReportSubmission;
use App\Repository\ReportRepository;
use App\Repository\ReportSubmissionRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Consider moving this to service classes, and unit test triggers.
 * There were some cases where those triggers failed.
 */
class DoctrineListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Report && !$entity->getId()) {
            /** @var ReportRepository $reportRepo */
            $reportRepo = $entityManager->getRepository(Report::class);
            $reportRepo->addDebtsToReportIfMissing($entity);
            $reportRepo->addMoneyShortCategoriesIfMissing($entity);
            $reportRepo->addFeesToReportIfMissing($entity);
        }

        if ($entity instanceof MoneyTransactionShortIn && !$entity->getId()) {
            $entity->getReport()->setMoneyTransactionsShortInExist('yes');
        }

        if ($entity instanceof MoneyTransactionShortOut && !$entity->getId()) {
            $entity->getReport()->setMoneyTransactionsShortOutExist('yes');
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof Document && !is_null($entity->getReportSubmission())) {
            /** @var ReportSubmissionRepository $reportSubmissionRepo */
            $reportSubmissionRepo = $entityManager->getRepository(ReportSubmission::class);
            $reportSubmissionRepo->updateArchivedStatus($entity->getReportSubmission());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MoneyTransactionShortIn) {
            $report = $entity->getReport();
            if (0 === count($report->getMoneyTransactionsShortIn())) {
                $report->setMoneyTransactionsShortInExist('no');
            }
        }

        if ($entity instanceof MoneyTransactionShortOut) {
            $report = $entity->getReport();
            if (0 === count($report->getMoneyTransactionsShortOut())) {
                $report->setMoneyTransactionsShortOutExist('no');
            }
        }

        if ($entity instanceof Contact) {
            $report = $entity->getReport();
            if (1 === count($report->getContacts())) {
                $report->setReasonForNoContacts(null);
            }
        }

        if ($entity instanceof Gift) {
            $report = $entity->getReport();
            if (1 === count($report->getGifts())) {
                $report->setGiftsExist(null);
            }
        }

        if ($entity instanceof Document) {
            $report = $entity->getReport();
            if ($report instanceof Report && 1 === count($report->getDocuments())) {
                $report->setWishToProvideDocumentation(null);
            }
        }

        if ($entity instanceof Decision) {
            $report = $entity->getReport();
            if (1 === count($report->getDecisions())) {
                $report->setReasonForNoDecisions(null);
            }
        }

        if ($entity instanceof Expense) {
            $report = $entity->getReport();
            if (1 === count($report->getExpenses())) {
                $report->setPaidForAnything(null);
            }
        }

        if ($entity instanceof MoneyTransfer) {
            $report = $entity->getReport();
            if (1 === count($report->getMoneyTransfers())) {
                $report->setNoTransfersToAdd(null);
            }
        }

        if ($entity instanceof Asset) {
            $report = $entity->getReport();
            if (1 === count($report->getAssets())) {
                $report->setNoAssetToAdd(null);
            }
        }

        if ($entity instanceof ProfServiceFeeCurrent) {
            $report = $entity->getReport();
            if (1 === count($report->getCurrentProfServiceFees())) {
                $report->setCurrentProfPaymentsReceived(null);
                $report->setPreviousProfFeesEstimateGiven(null);
                $report->setProfFeesEstimateSccoReason(null);
            }
        }
    }
}

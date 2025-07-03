<?php

namespace App\EventListener;

use App\Entity as EntityDir;
use App\Entity\Report\Report;
use App\Repository\NdrRepository;
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

        if ($entity instanceof EntityDir\Ndr\Ndr && !$entity->getId()) {
            $ndrRepo = $entityManager->getRepository('App\Entity\Ndr\Ndr');
            /* @var $ndrRepo NdrRepository */
            $ndrRepo->addDebtsToNdrIfMissing($entity);
            $ndrRepo->addIncomeBenefitsToNdrIfMissing($entity);
        }

        if ($entity instanceof EntityDir\Report\MoneyTransactionShortIn && !$entity->getId()) {
            $entity->getReport()->setMoneyTransactionsShortInExist('yes');
        }

        if ($entity instanceof EntityDir\Report\MoneyTransactionShortOut && !$entity->getId()) {
            $entity->getReport()->setMoneyTransactionsShortOutExist('yes');
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof EntityDir\Report\Document && !is_null($entity->getReportSubmission())) {
            /** @var ReportSubmissionRepository $reportSubmissionRepo */
            $reportSubmissionRepo = $entityManager->getRepository(EntityDir\Report\ReportSubmission::class);
            $reportSubmissionRepo->updateArchivedStatus($entity->getReportSubmission());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof EntityDir\Report\MoneyTransactionShortIn) {
            $report = $entity->getReport();
            if (0 === count($report->getMoneyTransactionsShortIn())) {
                $report->setMoneyTransactionsShortInExist('no');
            }
        }

        if ($entity instanceof EntityDir\Report\MoneyTransactionShortOut) {
            $report = $entity->getReport();
            if (0 === count($report->getMoneyTransactionsShortOut())) {
                $report->setMoneyTransactionsShortOutExist('no');
            }
        }

        if ($entity instanceof EntityDir\Report\Contact) {
            $report = $entity->getReport();
            if (1 === count($report->getContacts())) {
                $report->setReasonForNoContacts(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Gift) {
            $report = $entity->getReport();
            if (1 === count($report->getGifts())) {
                $report->setGiftsExist(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Document) {
            $report = $entity->getReport();
            if ($report instanceof Report && 1 === count($report->getDocuments())) {
                $report->setWishToProvideDocumentation(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Decision) {
            $report = $entity->getReport();
            if (1 === count($report->getDecisions())) {
                $report->setReasonForNoDecisions(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Expense) {
            $report = $entity->getReport();
            if (1 === count($report->getExpenses())) {
                $report->setPaidForAnything(null);
            }
        }

        if ($entity instanceof EntityDir\Report\MoneyTransfer) {
            $report = $entity->getReport();
            if (1 === count($report->getMoneyTransfers())) {
                $report->setNoTransfersToAdd(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Asset) {
            $report = $entity->getReport();
            if (1 === count($report->getAssets())) {
                $report->setNoAssetToAdd(null);
            }
        }

        if ($entity instanceof EntityDir\Report\ProfServiceFeeCurrent) {
            $report = $entity->getReport();
            if (1 === count($report->getCurrentProfServiceFees())) {
                $report->setCurrentProfPaymentsReceived(null);
                $report->setPreviousProfFeesEstimateGiven(null);
                $report->setProfFeesEstimateSccoReason(null);
            }
        }

        // NDR
        if ($entity instanceof EntityDir\Ndr\Expense) {
            $ndr = $entity->getNdr();
            if (1 === count($ndr->getExpenses())) {
                $ndr->setPaidForAnything(null);
            }
        }

        if ($entity instanceof EntityDir\Ndr\Asset) {
            $ndr = $entity->getNdr();
            if (1 === count($ndr->getAssets())) {
                $ndr->setNoAssetToAdd(null);
            }
        }
    }
}

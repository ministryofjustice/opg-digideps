<?php

namespace OPG\Digideps\Backend\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use OPG\Digideps\Backend\Entity\Report\Asset;
use OPG\Digideps\Backend\Entity\Report\Contact;
use OPG\Digideps\Backend\Entity\Report\Decision;
use OPG\Digideps\Backend\Entity\Report\Document;
use OPG\Digideps\Backend\Entity\Report\Expense;
use OPG\Digideps\Backend\Entity\Report\Gift;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortIn;
use OPG\Digideps\Backend\Entity\Report\MoneyTransactionShortOut;
use OPG\Digideps\Backend\Entity\Report\MoneyTransfer;
use OPG\Digideps\Backend\Entity\Report\ProfServiceFeeCurrent;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\Report\ReportSubmission;
use OPG\Digideps\Backend\Repository\ReportRepository;
use OPG\Digideps\Backend\Repository\ReportSubmissionRepository;

/**
 * Consider moving this to service classes, and unit test triggers.
 * There were some cases where those triggers failed.
 */
class DoctrineListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        if ($entity instanceof Report && !$entity->getId()) {
            /** @var ReportRepository $reportRepo */
            $reportRepo = $entityManager->getRepository(Report::class);
            $reportRepo->addDebtsToReportIfMissing($entity);
            $reportRepo->addFeesToReportIfMissing($entity);
            $entity->setMoneyShortCategories($reportRepo->getMissingMoneyShortCategories($entity));
        }

        if ($entity instanceof MoneyTransactionShortIn && !$entity->getId()) {
            $entity->getReport()->setMoneyTransactionsShortInExist('yes');
        }

        if ($entity instanceof MoneyTransactionShortOut && !$entity->getId()) {
            $entity->getReport()->setMoneyTransactionsShortOutExist('yes');
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $entityManager = $args->getObjectManager();

        if ($entity instanceof Document && !is_null($entity->getReportSubmission())) {
            /** @var ReportSubmissionRepository $reportSubmissionRepo */
            $reportSubmissionRepo = $entityManager->getRepository(ReportSubmission::class);
            $reportSubmissionRepo->updateArchivedStatus($entity->getReportSubmission());
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof MoneyTransactionShortIn) {
            $report = $entity->getReport();
            if (count($report->getMoneyTransactionsShortIn()) === 0) {
                $report->setMoneyTransactionsShortInExist('no');
            }
        }

        if ($entity instanceof MoneyTransactionShortOut) {
            $report = $entity->getReport();
            if (count($report->getMoneyTransactionsShortOut()) === 0) {
                $report->setMoneyTransactionsShortOutExist('no');
            }
        }

        if ($entity instanceof Contact) {
            $report = $entity->getReport();
            if (count($report->getContacts()) === 1) {
                $report->setReasonForNoContacts(null);
            }
        }

        if ($entity instanceof Gift) {
            $report = $entity->getReport();
            if (count($report->getGifts()) === 1) {
                $report->setGiftsExist(null);
            }
        }

        if ($entity instanceof Document) {
            $report = $entity->getReport();
            if ($report instanceof Report && count($report->getDocuments()) === 1) {
                $report->setWishToProvideDocumentation(null);
            }
        }

        if ($entity instanceof Decision) {
            $report = $entity->getReport();
            if (count($report->getDecisions()) === 1) {
                $report->setReasonForNoDecisions(null);
            }
        }

        if ($entity instanceof Expense) {
            $report = $entity->getReport();
            if (count($report->getExpenses()) === 1) {
                $report->setPaidForAnything(null);
            }
        }

        if ($entity instanceof MoneyTransfer) {
            $report = $entity->getReport();
            if (count($report->getMoneyTransfers()) === 1) {
                $report->setNoTransfersToAdd(null);
            }
        }

        if ($entity instanceof Asset) {
            $report = $entity->getReport();
            if (count($report->getAssets()) === 1) {
                $report->setNoAssetToAdd(null);
            }
        }

        if ($entity instanceof ProfServiceFeeCurrent) {
            $report = $entity->getReport();
            if (count($report->getCurrentProfServiceFees()) === 1) {
                $report->setCurrentProfPaymentsReceived(null);
                $report->setPreviousProfFeesEstimateGiven(null);
                $report->setProfFeesEstimateSccoReason(null);
            }
        }
    }
}

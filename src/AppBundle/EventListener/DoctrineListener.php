<?php

namespace AppBundle\EventListener;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Consider moving this to service classes, and unit test triggers.
 * There were some cases where those triggers failed
 */
class DoctrineListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof EntityDir\Report\Report && !$entity->getId()) {
            $reportRepo = $entityManager->getRepository('AppBundle\Entity\Report\Report'); /* @var $reportRepo EntityDir\Repository\ReportRepository */
            $reportRepo->addDebtsToReportIfMissing($entity);
            $reportRepo->addMoneyShortCategoriesIfMissing($entity);
            $reportRepo->addFeesToReportIfMissing($entity);
        }

        if ($entity instanceof EntityDir\Ndr\Ndr && !$entity->getId()) {
            $ndrRepo = $entityManager->getRepository('AppBundle\Entity\Ndr\Ndr');
            /* @var $ndrRepo EntityDir\Ndr\NdrRepository */
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

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof EntityDir\Report\MoneyTransactionShortIn) {
            $report = $entity->getReport();
            if (count($report->getMoneyTransactionsShortIn()) === 1) {
                $report->setMoneyTransactionsShortInExist('no');
            }
        }

        if ($entity instanceof EntityDir\Report\MoneyTransactionShortOut) {
            $report = $entity->getReport();
            if (count($report->getMoneyTransactionsShortOut()) === 1) {
                $report->setMoneyTransactionsShortOutExist('no');
            }
        }

        if ($entity instanceof EntityDir\Report\Contact) {
            $report = $entity->getReport();
            if (count($report->getContacts()) === 1) {
                $report->setReasonForNoContacts(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Gift) {
            $report = $entity->getReport();
            if (count($report->getGifts()) === 1) {
                $report->setGiftsExist(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Document) {
            $report = $entity->getReport();
            if ($report instanceof Report && count($report->getDocuments()) === 1) {
                $report->setWishToProvideDocumentation(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Decision) {
            $report = $entity->getReport();
            if (count($report->getDecisions()) === 1) {
                $report->setReasonForNoDecisions(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Expense) {
            $report = $entity->getReport();
            if (count($report->getExpenses()) === 1) {
                $report->setPaidForAnything(null);
            }
        }

        if ($entity instanceof EntityDir\Report\MoneyTransfer) {
            $report = $entity->getReport();
            if (count($report->getMoneyTransfers()) === 1) {
                $report->setNoTransfersToAdd(null);
            }
        }

        if ($entity instanceof EntityDir\Report\Asset) {
            $report = $entity->getReport();
            if (count($report->getAssets()) === 1) {
                $report->setNoAssetToAdd(null);
            }
        }

        if ($entity instanceof EntityDir\Report\ProfServiceFeeCurrent) {
            $report = $entity->getReport();
            if (count($report->getCurrentProfServiceFees()) === 1) {
                $report->setCurrentProfPaymentsReceived(null);
            }
        }

        // NDR
        if ($entity instanceof EntityDir\Ndr\Expense) {
            $ndr = $entity->getNdr();
            if (count($ndr->getExpenses()) === 1) {
                $ndr->setPaidForAnything(null);
            }
        }

        if ($entity instanceof EntityDir\Ndr\Asset) {
            $ndr = $entity->getNdr();
            if (count($ndr->getAssets()) === 1) {
                $ndr->setNoAssetToAdd(null);
            }
        }


    }
}

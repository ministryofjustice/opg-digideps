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
        }

        if ($entity instanceof EntityDir\Odr\Odr && !$entity->getId()) {
            $odrRepo = $entityManager->getRepository('AppBundle\Entity\Odr\Odr');
            /* @var $odrRepo EntityDir\Odr\OdrRepository */
            $odrRepo->addDebtsToOdrIfMissing($entity);
            $odrRepo->addIncomeBenefitsToOdrIfMissing($entity);
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

        // NDR
        if ($entity instanceof EntityDir\Odr\Expense) {
            $odr = $entity->getOdr();
            if (count($odr->getExpenses()) === 1) {
                $odr->setPaidForAnything(null);
            }
        }

        if ($entity instanceof EntityDir\Odr\Asset) {
            $odr = $entity->getOdr();
            if (count($odr->getAssets()) === 1) {
                $odr->setNoAssetToAdd(null);
            }
        }
    }
}

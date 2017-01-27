<?php

namespace AppBundle\EventListener;

use AppBundle\Entity as EntityDir;
use Doctrine\ORM\Event\LifecycleEventArgs;

class DoctrineListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof EntityDir\Report\Report && !$entity->getId()) {
            $reportRepo = $entityManager->getRepository('AppBundle\Entity\Report\Report'); /* @var $reportRepo EntityDir\Report\ReportRepository */
            $reportRepo->addDebtsToReportIfMissing($entity);
            $reportRepo->addMoneyShortCategoriesIfMissing($entity);
        }

        if ($entity instanceof EntityDir\Odr\Odr && !$entity->getId()) {
            $odrRepo = $entityManager->getRepository('AppBundle\Entity\Odr\Odr');
            /* @var $odrRepo EntityDir\Odr\OdrRepository */
            $odrRepo->addDebtsToOdrIfMissing($entity);
            $odrRepo->addIncomeBenefitsToOdrIfMissing($entity);
        }
    }
}

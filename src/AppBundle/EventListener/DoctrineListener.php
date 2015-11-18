<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use AppBundle\Entity\Report;

class DoctrineListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // add empty transactions to report at creation time
        if ($entity instanceof Report && !$entity->getId() && count($entity->getTransactions())===0) {
            $entityManager->getRepository('AppBundle\Entity\Report')->addEmptyTransactionsToReport($entity);
        }
    }
}
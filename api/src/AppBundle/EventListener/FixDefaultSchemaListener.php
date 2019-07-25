<?php

namespace AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class FixDefaultSchemaListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return ['postGenerateSchema'];
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $schema = $args->getSchema();

        if (!$schema->hasNamespace('public')) {
            $schema->createNamespace('public');
        }
    }
}

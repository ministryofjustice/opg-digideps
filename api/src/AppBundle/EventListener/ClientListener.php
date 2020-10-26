<?php declare(strict_types=1);


namespace AppBundle\EventListener;

use AppBundle\Entity\Client;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class ClientListener
{
    /** @var array */
    public $logEvents = [];

    public function preUpdate(Client $client, PreUpdateEventArgs $args)
    {
        $changes = $args->getEntityChangeSet();
    }

    public function postUpdate(Client $client, LifecycleEventArgs $args)
    {
        $args->getEntity(); // to get entity
        $changes = 'sss';
    }
}

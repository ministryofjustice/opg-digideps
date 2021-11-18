<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\CasRec;
use App\Entity\User;
use App\Event\CSVUploadedEvent;
use App\Service\Audit\AuditEvents;
use App\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CSVUploadedSubscriber implements EventSubscriberInterface
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        DateTimeProvider $dateTimeProvider,
        LoggerInterface $logger
    ) {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            CSVUploadedEvent::NAME => 'doSomething1',
            CSVUploadedEvent::NAME => 'doSomething2',
        ];
    }

    public function doSomething1(CSVUploadedEvent $event)
    {
        if (User::TYPE_LAY == $event->getRoleType()) {
            if (CasRec::SIRIUS_SOURCE == $event->getSource()) {
                //Do Something
            } elseif (CasRec::CASREC_SOURCE == $event->getSource()) {
                //Do Something
            }
        } elseif (User::TYPE_PROF == $event->getRoleType()) {
            //Do Something
        } elseif (User::TYPE_PA == $event->getRoleType()) {
            //Do Something
        } else {
            //Throw Error
        }
    }

    public function doSomething2(CSVUploadedEvent $event)
    {
        $csvUploadedEvent = (new AuditEvents($this->dateTimeProvider))
            ->csvUploaded(
                $event->getTrigger(),
                $event->getSource(),
                $event->getRoleType()
            );

        $this->logger->notice('', $csvUploadedEvent);
    }
}

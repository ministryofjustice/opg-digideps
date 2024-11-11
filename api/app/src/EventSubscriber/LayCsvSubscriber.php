<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Event\MultiClientCreationEvent;
use App\Event\PreRegistrationCompletedEvent;
use App\Service\Client\LayCsvEvents;
use App\Service\Time\DateTimeProvider;
use App\v2\Registration\Uploader\LayDeputyshipUploader;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LayCsvSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger, 
        private readonly DateTimeProvider $dateTimeProvider,
        private readonly LayDeputyshipUploader $layUploader
    ) {
        
    }
    
    public static function getSubscribedEvents()
    {
        return [
            PreRegistrationCompletedEvent::NAME => [['logEvent'], ['multiClientProcessing']],
            MultiClientCreationEvent::NAME => 'logEvent'
        ];
    }
    
    public function logEvent(PreRegistrationCompletedEvent|MultiClientCreationEvent $event)
    {
        $this->logger->notice('', (new LayCsvEvents($this->dateTimeProvider))->csvProcessingEvent(
            $event->getTrigger(),
            $event->getJobName(),
            $event->getCompletionState(),
            $event->getProcessedOutput(),
        ));
    }
    
    public function multiClientProcessing(PreRegistrationCompletedEvent $event)
    {   // checking string is just failure due to possibility of a partial
        // where multiclient creation can still happen
        if (!preg_match('/^failure$/', $event->getCompletionState())) {
            $this->layUploader->multiClientCreation();
        }
    }
}

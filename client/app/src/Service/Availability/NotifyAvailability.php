<?php

declare(strict_types=1);

namespace App\Service\Availability;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\NotifyException;

class NotifyAvailability extends ServiceAvailabilityAbstract
{
    /**
     * @var NotifyClient
     */
    private $notifyClient;

    public function __construct(NotifyClient $notifyClient)
    {
        $this->notifyClient = $notifyClient;
        $this->isHealthy = true;
    }

    public function ping()
    {
        try {
            $this->pingNotify();
        } catch (NotifyException $e) {
            $this->isHealthy = false;
            $this->errors = sprintf('Notify - %s', $e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Notify';
    }

    public function pingNotify()
    {
        return $this->notifyClient->listTemplates();
    }
}

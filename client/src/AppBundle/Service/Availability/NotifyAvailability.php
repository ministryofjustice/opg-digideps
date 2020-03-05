<?php declare(strict_types=1);


namespace AppBundle\Service\Availability;

use Alphagov\Notifications\Client as NotifyClient;
use Alphagov\Notifications\Exception\ApiException as NotifyAPIException;

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

        try{
            $this->pingNotify();
        } catch (NotifyAPIException $e) {
            $this->isHealthy = false;
            $this->errors = $e->getErrorMessage();
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

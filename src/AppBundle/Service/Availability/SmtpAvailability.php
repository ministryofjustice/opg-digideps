<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SmtpAvailability extends ServiceAvailabilityAbstract
{
    private $transportKey;

    public function __construct(ContainerInterface $container, $transportKey)
    {
        $this->transportKey = $transportKey;
        $transport = $container->get($this->transportKey); /* @var $transport \Swift_SmtpTransport */

        $this->isHealthy = false;
        $this->errors = '';

        try {
            $transport->start();
            $transport->stop();
        } catch (\Exception $e) {
            $this->isHealthy = false;
            $this->errors = str_replace($transport->getHost(), '**********', $e->getMessage());
        }
    }

    public function getName()
    {
        return 'Smtp (' . $this->transportKey . ')';
    }
}

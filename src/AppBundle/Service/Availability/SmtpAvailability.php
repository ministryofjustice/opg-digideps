<?php

namespace AppBundle\Service\Availability;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SmtpAvailability extends ServiceAvailabilityAbstract
{
    private $transportKey;

    public function __construct(ContainerInterface $container, $transportKey)
    {
        $this->transportKey = $transportKey;

        try {
            $transport = $container->get($this->transportKey); /* @var $transport \Swift_SmtpTransport */
            $transport->start();
            $transport->stop();

            $this->isHealthy = true;
            $this->errors = '';
        } catch (\Exception $e) {
            $this->isHealthy = false;
            $this->errors = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Smtp ('.$this->transportKey.')';
    }
}

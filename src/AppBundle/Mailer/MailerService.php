<?php
namespace AppBundle\Mailer;

use AppBundle\Mailer\Filter\MessageFilterInterface;
use Swift_Mailer as Mailer;
use Swift_Mime_Message as Message;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Mailer service
 */
class MailerService extends Mailer
{
    /**
     * @param string $transport
     * @param Swift_Transport[] $transports
     */
    public function __construct($transport, array $transports)
    {
        if (empty($transports[$transport])) {
            throw new \Exception("Mail transport $transport not defined.Available transports: " . implode(', ', array_keys($transports)));
        }
        parent::__construct($transports[$transport]);
    }
    
}

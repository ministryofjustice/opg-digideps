<?php

namespace App\Service\Mailer;

use Alphagov\Notifications\Client;
use Alphagov\Notifications\Exception\NotifyException;
use Psr\Log\LoggerInterface;
use Throwable;

class NotifyClientMock extends Client
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /** @var array */
    private $sentMails = [];

    public function __construct(array $config, LoggerInterface $logger)
    {
        $this->logger = $logger;

        try {
            parent::__construct($config);
        } catch (Throwable $e) {
            // Swallow
        }
    }

    public function sendEmail($emailAddress, $templateId, array $personalisation = [], $reference = '', $emailReplyToId = null)
    {
        if ('break@publicguardian.gov.uk' === $emailAddress) {
            throw new NotifyException('Intentional mock exception');
        } else {
            try {
                $this->sentMails[$templateId] = $personalisation;
                parent::sendEmail($emailAddress, $templateId, $personalisation, $reference, $emailReplyToId);
            } catch (Throwable $e) {
                $this->logger->warning('Mocked email, but received Notify error: '.$e->getMessage());
            }

            return [];
        }
    }

    public function getSentEmails()
    {
        return $this->sentMails;
    }
}

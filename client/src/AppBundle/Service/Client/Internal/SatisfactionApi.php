<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;

class SatisfactionApi
{
    const CREATE_PUBLIC_ENDPOINT = 'satisfaction/public';

    /** @var RestClient */
    private $restClient;

    /** @var MailFactory */
    private $mailFactory;

    /** @var MailSender */
    private $mailSender;

    public function __construct(RestClient $restClient, MailFactory $mailFactory, MailSender $mailSender)
    {
        $this->restClient = $restClient;
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
    }

    public function create(array $formResponse)
    {
        $this->restClient->post(
            self::CREATE_PUBLIC_ENDPOINT,
            ['satisfactionLevel' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );

        $feedbackEmail = $this->mailFactory->createGeneralFeedbackEmail($formResponse);
        $this->mailSender->send($feedbackEmail);
    }
}

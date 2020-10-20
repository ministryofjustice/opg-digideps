<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;

class SatisfactionApi
{
    const CREATE_PUBLIC_ENDPOINT = 'satisfaction/public';

    /** @var RestClient */
    private $restClient;

    public function __construct(RestClientInterface $restClient)
    {
        $this->restClient = $restClient;
    }

    public function create(array $formResponse)
    {
        $this->restClient->post(
            self::CREATE_PUBLIC_ENDPOINT,
            ['satisfactionLevel' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );
    }
}

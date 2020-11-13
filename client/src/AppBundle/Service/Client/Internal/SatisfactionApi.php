<?php declare(strict_types=1);

namespace AppBundle\Service\Client\Internal;

use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Client\RestClientInterface;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;

class SatisfactionApi
{
    private const CREATE_PUBLIC_ENDPOINT = 'satisfaction/public';

    /** @var RestClient */
    private $restClient;

    public function __construct(RestClientInterface $restClient)
    {
        $this->restClient = $restClient;
    }

    /**
     * @param array $formResponse
     */
    public function create(array $formResponse): void
    {
        $this->restClient->post(
            self::CREATE_PUBLIC_ENDPOINT,
            ['score' => $formResponse['satisfactionLevel'], 'comments' => $formResponse['comments']]
        );
    }
}

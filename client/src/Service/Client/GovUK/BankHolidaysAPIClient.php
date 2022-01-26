<?php

declare(strict_types=1);

namespace App\Service\Client\GovUK;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;

class BankHolidaysAPIClient
{
    public const BANK_HOLIDAYS_ENDPOINT = 'https://www.gov.uk/bank-holidays.json';

    /** @var Client */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Client $httpClient,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBankHolidays()
    {
        $request = new Request('GET', self::BANK_HOLIDAYS_ENDPOINT);

        return $this->httpClient->send($request, ['connect_timeout' => 1, 'timeout' => 1.5]);
    }
}

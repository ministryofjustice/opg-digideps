<?php

declare(strict_types=1);

namespace App\Service\Client\GovUK;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;

class BankHolidaysAPIClient
{
    public const BANK_HOLIDAYS_ENDPOINT = 'https://www.gov.uk/bank-holidays.json';

    /** @var ClientInterface */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getBankHolidays()
    {
        $request = new Request('GET', self::BANK_HOLIDAYS_ENDPOINT);

        $response = $this->httpClient->send($request, ['connect_timeout' => 1, 'timeout' => 1.5]);

        return json_decode((string) $response->getBody(), true);
    }
}
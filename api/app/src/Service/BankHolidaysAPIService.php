<?php

namespace App\Service;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class BankHolidaysAPIService
{
    public const BANK_HOLIDAYS_ENDPOINT = 'https://www.gov.uk/bank-holidays.json';
    private ClientInterface $httpClient;

    public function __construct(
        ClientInterface $httpClient,
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws GuzzleException
     */
    public function getBankHolidays(): array
    {
        $request = new Request('GET', self::BANK_HOLIDAYS_ENDPOINT);

        $response = $this->httpClient->send($request, ['connect_timeout' => 1, 'timeout' => 3]);

        $responseDecoded = json_decode((string) $response->getBody(), true);

        return $responseDecoded;
    }
}

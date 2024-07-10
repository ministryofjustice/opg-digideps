<?php

namespace App\Service;

use Cmixin\BusinessDay;
use GuzzleHttp\Exception\GuzzleException;

class CarbonBusinessDaysService
{
    private string $baseList;

    /**
     * @throws GuzzleException
     */
    public function __construct(
        private readonly BankHolidaysAPIService $bankHolidaysAPIService,
        string $baseList = 'gb-engwales'
    ) {
        $this->baseList = $baseList;
        $this->load();
    }

    /**
     * @throws GuzzleException
     */
    public function load(): void
    {
        $additionalHolidays = $this->bankHolidaysAPIService->getBankHolidays();

        BusinessDay::enable('Carbon\Carbon', $this->baseList, $additionalHolidays);
    }
}

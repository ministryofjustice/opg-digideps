<?php

namespace App\Service;

use Carbon\Carbon;
use Cmixin\BusinessDay;

class CarbonBusinessDaysService
{
    private string $baseList;
    private BankHolidaysAPIService $bankHolidaysAPIService;

    public function __construct(BankHolidaysAPIService $bankHolidaysAPIService, string $baseList = 'gb-engwales')
    {
        $this->baseList = $baseList;
        $this->bankHolidaysAPIService = $bankHolidaysAPIService;
        $this->load();
    }

    private function load(): void
    {
        $additionalHolidays = [
            'early-may-duplicate' => '2024-05-06',
            'test-date' => '2024-05-17',
            'test-date-2' => '2024-05-20',
        ];

        BusinessDay::enable(['Carbon\_ide_business_day_instantiated', 'Carbon\CarbonImmutable'], $this->baseList, $additionalHolidays);

        Carbon::setHolidaysRegion($this->baseList);
    }

    public static function addBusinessDays($endDate)
    {
        return Carbon::parse($endDate)->addBusinessDays('15')->format('Y-m-d H:i:s');
    }
}

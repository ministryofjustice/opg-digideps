<?php

namespace App\Service;

class BankHolidaysAPIService
{
    public const BANK_HOLIDAYS_FILE_LOCATION = '/tmp/bank-holidays.json';

    public function getBankHolidays(): array
    {
        $bankHolidayJson = file_get_contents(self::BANK_HOLIDAYS_FILE_LOCATION);
        $jsonDecoded = json_decode($bankHolidayJson, true);

        $englandAndWalesDates = [];

        foreach ($jsonDecoded['england-and-wales']['events'] as $bankHoliday) {
            $englandAndWalesDates[$bankHoliday['title']] = $bankHoliday['date'];
        }

        return $englandAndWalesDates;
    }
}

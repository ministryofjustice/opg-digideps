<?php

namespace App\Service;

class BankHolidaysAPIService
{
    public const BANK_HOLIDAYS_FILE_LOCATION = '/tmp/bank-holidays.json';

    /**
     * @throws \Exception
     */
    public function getBankHolidays(): array
    {
        if (!file_exists(self::BANK_HOLIDAYS_FILE_LOCATION)) {
            throw new \Exception('Bank holidays file does not exist');
        }

        $bankHolidayJson = file_get_contents(self::BANK_HOLIDAYS_FILE_LOCATION);
        $jsonDecoded = json_decode($bankHolidayJson, true);

        $englandAndWalesDates = [];

        foreach ($jsonDecoded['england-and-wales']['events'] as $bankHoliday) {
            $englandAndWalesDates[$bankHoliday['title']] = $bankHoliday['date'];
        }

        return $englandAndWalesDates;
    }
}

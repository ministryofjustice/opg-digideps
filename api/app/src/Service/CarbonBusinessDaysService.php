<?php

namespace App\Service;

use Carbon\Carbon;
use Cmixin\BusinessDay;

class CarbonBusinessDaysService
{
    private string $baseList;

    public function __construct(string $baseList = 'gb-engwales')
    {
        $this->baseList = $baseList;
        $this->load();
    }

    private function load(): void
    {
        BusinessDay::enable(['Carbon\Carbon', 'Carbon\CarbonImmutable'], $this->baseList);
        Carbon::setHolidaysRegion($this->baseList);
    }
}

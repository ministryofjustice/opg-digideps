<?php

declare(strict_types=1);

namespace App\v2\Registration\DeputyshipProcessing\Report;

use App\Factory\DataFactoryResult;

class ReportTypeUpdate
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'ReportTypeUpdate';
    }

    public function run(): DataFactoryResult
    {
        return new DataFactoryResult(messages: ['Success' => ['Updated report type post processing ran successfully']]);
    }
}

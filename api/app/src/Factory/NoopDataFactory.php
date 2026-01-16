<?php

declare(strict_types=1);

namespace App\Factory;

/**
 * A factory that does nothing and produces no data.
 */
class NoopDataFactory implements DataFactoryInterface
{
    public function getName(): string
    {
        return 'NoopDataFactory';
    }

    public function run(): DataFactoryResult
    {
        return new DataFactoryResult(messages: ['Success' => ['Noop data factory ran successfully']]);
    }
}

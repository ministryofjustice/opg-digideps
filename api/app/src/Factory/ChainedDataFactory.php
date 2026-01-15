<?php

declare(strict_types=1);

namespace App\Factory;

/**
 * Run multiple data factories in sequence and aggregate their outputs.
 */
class ChainedDataFactory implements DataFactoryInterface
{
    public function __construct()
    {
    }

    public function run(): DataFactoryResult
    {
        return new DataFactoryResult();
    }
}

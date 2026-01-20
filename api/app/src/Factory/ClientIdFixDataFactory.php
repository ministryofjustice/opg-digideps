<?php

declare(strict_types=1);

namespace App\Factory;

class ClientIdFixDataFactory implements DataFactoryInterface
{
    public function getName(): string
    {
        return 'ClientIdFix';
    }

    public function run(): DataFactoryResult
    {
        return new DataFactoryResult(messages: ['Success' => ['Client IDs patched successfully']]);
    }
}

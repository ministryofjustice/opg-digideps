<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Fixture;

final readonly class DeputySet
{
    /**
     * @var array<DeputyDescriptor>
     */
    public array $descriptors;

    public function __construct(DeputyDescriptor ...$descriptors)
    {
        $this->descriptors = $descriptors;
    }
}

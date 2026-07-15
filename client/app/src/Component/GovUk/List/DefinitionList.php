<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final readonly class DefinitionList
{
    public array $entries;

    public function __construct(Entry | BulletPointEntry ...$entries)
    {
        $this->entries = $entries;
    }
}

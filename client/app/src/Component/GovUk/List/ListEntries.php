<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final readonly class ListEntries
{
    public array $entries;

    public function __construct(Entry ...$entries)
    {
        $this->entries = $entries;
    }
}

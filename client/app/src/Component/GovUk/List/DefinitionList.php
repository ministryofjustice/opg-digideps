<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final readonly class DefinitionList
{
    public array $items;

    public function __construct(public Widths $widths, Item ...$items)
    {
        $this->items = $items;
    }
}

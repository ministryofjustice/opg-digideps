<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Summary;

final readonly class SummaryList
{
    public array $items;

    public function __construct(public Widths $widths, Item ...$items)
    {
        $this->items = $items;
    }
}

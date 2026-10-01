<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Breadcrumbs;

final readonly class Breadcrumbs
{
    /**
     * @var array<Item> $items
     */
    public array $items;

    public function __construct(Item ...$items)
    {
        $this->items = $items;
    }
}

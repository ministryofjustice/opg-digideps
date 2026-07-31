<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Breadcrumbs;

final class BreadcrumbsBuilder
{
    /**
     * @var array<Item>
     */
    private array $items;

    public function __construct()
    {
        $this->items = [];
    }

    public function addItem(string $href, string $text): BreadcrumbsBuilder
    {
        $this->items[] = new Item($href, $text);
        return $this;
    }

    public function makeBreadcrumbs(): Breadcrumbs
    {
        return new Breadcrumbs(...$this->items);
    }
}

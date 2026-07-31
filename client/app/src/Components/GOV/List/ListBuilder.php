<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\List;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class ListBuilder
{
    /**
     * @var array<string|RenderableInterface>
     */
    private array $items;

    public function __construct(private bool $decorated = true)
    {
    }

    public function addItem(string|RenderableInterface $item): ListBuilder
    {
        $this->items[] = $item;
        return $this;
    }

    public function makeUnorderedList(): UnorderedList
    {
        return new UnorderedList($this->decorated, ...$this->items);
    }

    public function makeOrderedList(): OrderedList
    {
        return new OrderedList($this->decorated, ...$this->items);
    }
}

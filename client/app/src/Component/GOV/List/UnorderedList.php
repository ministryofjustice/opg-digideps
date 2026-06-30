<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GOV\List;

use OPG\Digideps\Frontend\Component\RenderableInterface;

final class UnorderedList extends AbstractList
{
    public function __construct(bool $decorated, string|RenderableInterface ...$items)
    {
        parent::__construct(ListTag::Unordered, $decorated, ...$items);
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GOV\Summary;

use OPG\Digideps\Frontend\Component\RenderableInterface;

final readonly class Item
{
    public function __construct(
        public RenderableInterface|string $key,
        public RenderableInterface|string $value,
        public null $action = null
    ) {
    }
}

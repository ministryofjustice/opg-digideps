<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Summary;

use OPG\Digideps\Frontend\Components\RenderableInterface;
use Twig\Markup;

final readonly class Item
{
    public function __construct(
        public RenderableInterface|string $key,
        public Markup|RenderableInterface|string $value,
        public null $action = null
    ) {
    }
}

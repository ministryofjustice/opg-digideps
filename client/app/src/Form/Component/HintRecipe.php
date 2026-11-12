<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class HintRecipe
{
    public function __construct(
        public RenderableInterface|string $text,
    ) {
    }
}

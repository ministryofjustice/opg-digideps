<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

final readonly class HintRecipe
{
    public function __construct(
        public ?string $text = null,
    ) {
    }
}

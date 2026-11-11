<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

final readonly class LegendRecipe
{
    public function __construct(
        public ?string $text = null,
        public ?int $h = null,
        public string $size = 'm'
    ) {
    }
}

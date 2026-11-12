<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

final readonly class LabelRecipe
{
    public function __construct(
        public string $text,
        public ?int $h = null,
        public string $size = 'm'
    ) {
    }
}

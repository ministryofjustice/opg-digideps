<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Frontend\Components\RenderableInterface;

class Div implements RenderableInterface
{
    public function __construct(
        private readonly string $text,
        private readonly bool $isVisuallyHidden = false,
    ) {
    }

    public string $componentName {
        get => 'GOV:Div';
    }

    public array $props {
        get => [
            'text' => $this->text,
            'className' => $this->isVisuallyHidden ? 'govuk-visually-hidden' : '',
        ];
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

class Div implements RenderableInterface
{
    /**
     * @param array<string|RenderableInterface> $text
     */
    public function __construct(
        private readonly array $text = [],
        private readonly bool $isVisuallyHidden = false
    ) {
    }

    public string $componentName {
        get => 'GOV:Div';
    }

    public array $props {
        get => [
            'text' => $this->text,
            'isVisuallyHidden' => $this->isVisuallyHidden
        ];
    }
}

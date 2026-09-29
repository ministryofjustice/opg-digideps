<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Frontend\Components\RenderableInterface;

class Link implements RenderableInterface
{
    public function __construct(
        private readonly string $href,
        private readonly bool $inverse = false,
        private readonly bool $noVisitedState = false,
        private readonly bool $inNewTab = false,
        private readonly string|RenderableInterface|null $text = null,
    ) {
    }

    public string $componentName {
        get => 'GOV:Link';
    }

    public array $props {
        get => [
            'href' => $this->href,
            'inverse' => $this->inverse,
            'noVisitedState' => $this->noVisitedState,
            'inNewTab' => $this->inNewTab,
            'text' => $this->text,
        ];
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Frontend\Components\RenderableInterface;

class Link implements RenderableInterface
{
    public function __construct(
        private readonly string $href,
        private readonly string|RenderableInterface|null $text = null,
        private readonly bool $inverse = false,
        private readonly bool $noVisitedState = false,
        private readonly bool $inNewTab = false,
        private readonly ?string $accessibilityText = null,
    ) {
    }

    public string $componentName {
        get => 'GOV:Link';
    }

    public array $props {
        get => [
            'href' => $this->href,
            'text' => $this->text,
            'inverse' => $this->inverse,
            'noVisitedState' => $this->noVisitedState,
            'inNewTab' => $this->inNewTab,
            'accessibilityText' => $this->accessibilityText,
        ];
    }
}

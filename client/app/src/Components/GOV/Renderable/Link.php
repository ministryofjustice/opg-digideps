<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Link implements RenderableInterface
{
    public function __construct(
        private readonly string $text,
        private readonly string $href,
        private readonly bool $inNewTab = false
    ) {
    }

    public string $componentName {get => 'GOV:Renderable:Link';}
    public array $props {get => ['text' => $this->text, 'href' => $this->href, 'inNewTab' => $this->inNewTab];}
}

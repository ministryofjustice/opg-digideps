<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Heading implements RenderableInterface
{
    public function __construct(
        private readonly string $text,
        private readonly int $h = 1
    ) {
    }

    public string $componentName {get => 'GOV:Renderable:Heading';}
    public array $props {get => ['text' => $this->text, 'h' => $this->h];}
}

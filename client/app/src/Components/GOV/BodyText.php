<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class BodyText implements RenderableInterface
{
    public function __construct(
        private readonly string $text
    ) {
    }

    public string $componentName {get => 'GOV:BodyText';}
    public array $props {get => ['text' => $this->text];}
}

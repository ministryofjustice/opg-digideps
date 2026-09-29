<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Caption implements RenderableInterface
{
    public function __construct(
        private readonly string $text,
        private readonly string $size = "m",
        private readonly ?string $tag = null,
    ) {
    }

    public string $componentName {
        get => 'GOV:Caption';
    }

    public array $props {
        get => [
            'text' => $this->text,
            'size' => $this->size,
            'tag' => $this->tag,
        ];
    }
}

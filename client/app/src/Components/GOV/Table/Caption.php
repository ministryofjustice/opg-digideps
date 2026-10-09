<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Caption implements RenderableInterface
{
    public function __construct(
        private readonly string  $text,
        private readonly string  $size = "m",

        // GOV:Tag colour constant
        private readonly ?string $tagColour = null,
    ) {
    }

    public string $componentName {
        get => 'GOV:Table:Caption';
    }

    public array $props {
        get => [
            'text' => $this->text,
            'size' => $this->size,
            'tagColour' => $this->tagColour,
        ];
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Form;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class HiddenBox implements RenderableInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $value,
    ) {
    }

    public string $componentName { get => 'GOV:Form:HiddenBox'; }
    public array $props {
        get => [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
        ];
    }
}

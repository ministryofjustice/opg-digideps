<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Form;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Button implements RenderableInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $label,
        private readonly string $type = 'submit',
        private readonly string $kind = 'primary',
        private readonly bool $disabled = false,
        private readonly ?string $value = null,
    ) {
    }

    public string $componentName { get => 'GOV:Form:Button'; }
    public array $props {
        get => [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'kind' => $this->kind,
            'disabled' => $this->disabled,
            'value' => $this->value,
        ];
    }
}

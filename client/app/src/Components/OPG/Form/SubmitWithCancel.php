<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Form;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class SubmitWithCancel implements RenderableInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $label,
        private readonly string $cancelHref,
        private readonly string $cancelLabel
    ) {
    }

    public string $componentName { get => 'OPG:Form:SubmitWithCancel'; }
    public array $props {
        get => [
            'id' => $this->id,
            'name' => $this->name,
            'label' => $this->label,
            'cancelHref' => $this->cancelHref,
            'cancelLabel' => $this->cancelLabel
        ];
    }
}

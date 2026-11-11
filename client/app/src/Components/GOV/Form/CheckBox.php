<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Form;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class CheckBox implements RenderableInterface
{
    /**
     * @var array<string> $errors
     */
    private readonly array $errors;

    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $label,
        private readonly string $value,
        private readonly bool $checked,
        private readonly bool $required,
        private readonly bool $disabled,
        private readonly ?string $legend,
        private readonly ?int $hLegend,
        private readonly string $sizeLegend,
        private readonly ?string $hint,
        string ...$errors
    ) {
        $this->errors = $errors;
    }

    public string $componentName { get => 'GOV:Form:CheckBox'; }
    public array $props {
        get => [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'checked' => $this->checked,
            'label' => $this->label,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'legend' => $this->legend,
            'hLegend' => $this->hLegend,
            'sizeLegend' => $this->sizeLegend,
            'hint' => $this->hint,
            'errors' => empty($this->errors) ? null : $this->errors,
        ];
    }
}

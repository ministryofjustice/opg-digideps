<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Form;

use OPG\Digideps\Frontend\Components\RenderableInterface;
use OPG\Digideps\Frontend\Form\Component\HintRecipe;
use OPG\Digideps\Frontend\Form\Component\LabelRecipe;
use OPG\Digideps\Frontend\Form\Component\LegendRecipe;

final class TextBox implements RenderableInterface
{
    /**
     * @var ?array<string> $errors
     */
    private readonly ?array $errors;

    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $type,
        private readonly LabelRecipe $label,
        private readonly ?string $prefix,
        private readonly ?string $suffix,
        private readonly string $value,
        private readonly ?int $characters,
        private readonly bool $required,
        private readonly bool $disabled,
        private readonly ?LegendRecipe $legend,
        private readonly ?HintRecipe $hint,
        string ...$errors
    ) {
        $this->errors = empty($errors) ? null : $errors;
    }

    public string $componentName { get => 'GOV:Form:TextBox'; }
    public array $props {
        get => [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'label' => $this->label,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'characters' => $this->characters,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'legend' => $this->legend,
            'hint' => $this->hint,
            'errors' => $this->errors,
        ];
    }
}

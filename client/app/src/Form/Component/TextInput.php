<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\GOV\Form\TextBox;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final readonly class TextInput extends AbstractInput
{
    public function __construct(
        string $name,
        LabelRecipe|string $label,
        bool $required = true,
        bool $disabled = false,
        public ?int $characters = null,
        ?LegendRecipe $legend = null,
        HintRecipe|string|null $hint = null,
        public ?string $prefix = null,
        public ?string $suffix = null,
        ?Constraints $constraints = null
    ) {
        parent::__construct(
            TextType::class,
            $name,
            $label,
            $legend,
            $hint,
            $required,
            $disabled,
            $constraints
        );
    }

    public function render(ValidatingArray $variables): RenderableInterface
    {
        return new TextBox(
            $variables->getStringOrThrow('id'),
            $variables->getStringOrThrow('full_name'),
            'text',
            $this->label,
            $this->prefix,
            $this->suffix,
            $variables->getStringOrDefault('data', ''),
            $this->characters,
            $this->required,
            $this->disabled,
            $this->legend,
            $this->hint,
            ...AbstractInput::extractErrorMessages($variables)
        );
    }
}

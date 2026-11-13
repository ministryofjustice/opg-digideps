<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\GOV\Form\CheckBox;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

final readonly class CheckBoxInput extends AbstractInput
{
    public function __construct(
        string $name,
        string $label,
        bool $required = true,
        bool $disabled = false,
        ?LegendRecipe $legend = null,
        ?HintRecipe $hint = null,
        ?Constraints $constraints = null
    ) {
        parent::__construct(
            CheckboxType::class,
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
        return new CheckBox(
            $variables->getStringOrThrow('id'),
            $variables->getStringOrThrow('full_name'),
            $this->label->text,
            $variables->getStringOrThrow('value'),
            $variables->getBooleanOrThrow('data'),
            $this->required,
            $this->disabled,
            $this->legend,
            $this->hint,
            ...AbstractInput::extractErrorMessages($variables)
        );
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\GOV\Form\TextBox;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

final readonly class PasswordInput extends AbstractInput
{
    public function __construct(
        string $name,
        LabelRecipe|string $label,
        bool $required = true,
        bool $disabled = false,
        public ?int $characters = null,
        ?LegendRecipe $legend = null,
        ?HintRecipe $hint = null,
        ?Constraints $constraints = null
    ) {
        parent::__construct(
            EmailType::class,
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
            'password',
            $this->label,
            null,
            null,
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

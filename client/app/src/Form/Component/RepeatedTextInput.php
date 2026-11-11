<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use Symfony\Component\Form\Extension\Core\Type\TextType;

final readonly class RepeatedTextInput extends AbstractRepeatedInput
{
    public function __construct(
        string $name,
        string $label,
        string $repeatLabel,
        string $repeatError,
        bool $required = true,
        bool $disabled = false,
        public ?int $characters = null,
        ?LegendRecipe $legend = null,
        ?HintRecipe $hint = null,
        ?Constraints $constraints = null,
    ) {
        parent::__construct(
            TextType::class,
            $name,
            $label,
            $legend,
            $hint,
            $required,
            $disabled,
            $constraints,
            $repeatLabel,
            $repeatError
        );
    }

    protected function makeChild(string $name, string $label, ?Constraints $constraints): AbstractInput
    {
        return new TextInput(
            $name,
            $label,
            $this->required,
            $this->disabled,
            $this->characters,
            $this->legend,
            $this->hint,
            $constraints
        );
    }
}

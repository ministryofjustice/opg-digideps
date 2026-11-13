<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use Symfony\Component\Form\Extension\Core\Type\EmailType;

final readonly class RepeatedEmailInput extends AbstractRepeatedInput
{
    public function __construct(
        string $name,
        LabelRecipe|string $label,
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
            EmailType::class,
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

    protected function makeChild(string $name, LabelRecipe $label, ?Constraints $constraints): AbstractInput
    {
        return new EmailInput(
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

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\GOV\Form\Button;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

final readonly class SubmitButton extends AbstractInput
{
    public function __construct(string $name, string $label)
    {
        parent::__construct(
            SubmitType::class,
            $name,
            $label,
            null,
            null,
            false,
            false,
            null
        );
    }

    public function render(ValidatingArray $variables): RenderableInterface
    {
        return new Button(
            $variables->getStringOrThrow('id'),
            $variables->getStringOrThrow('full_name'),
            $this->label->text
        );
    }
}

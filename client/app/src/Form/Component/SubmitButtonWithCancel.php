<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\OPG\Form\SubmitWithCancel;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * A submit button with a cancel link to $cancelUrl alongside it.
 */
final readonly class SubmitButtonWithCancel extends AbstractInput
{
    public function __construct(string $name, string $label, private string $cancelLabel, private string $cancelUrl)
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
        return new SubmitWithCancel(
            $variables->getStringOrThrow('id'),
            $variables->getStringOrThrow('full_name'),
            $this->label->text,
            $this->cancelUrl,
            $this->cancelLabel
        );
    }
}

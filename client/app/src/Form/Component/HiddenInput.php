<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\GOV\Form\HiddenBox;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

final readonly class HiddenInput extends AbstractInput
{
    public function __construct(string $name)
    {
        parent::__construct(
            HiddenType::class,
            $name,
            '',
            null,
            null,
            false,
            false,
            null
        );
    }

    public function render(ValidatingArray $variables): RenderableInterface
    {
        return new HiddenBox(
            $variables->getStringOrThrow('id'),
            $variables->getStringOrThrow('full_name'),
            $variables->getStringOrThrow('data'),
        );
    }
}

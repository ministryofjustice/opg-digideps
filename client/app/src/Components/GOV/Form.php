<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\FormView;

final readonly class Form implements RenderableInterface
{
    public array $props;
    public string $componentName;

    public function __construct(
        public FormView $view,
        /**
         * @var array<string> $errors
         */
        public array $errors,
        RenderableInterface ...$inputs
    ) {
        $variables = new ValidatingArray((array)$this->view->vars);
        $this->props = [
            'id' => $variables->getStringOrThrow('id'),
            'name' => $variables->getStringOrThrow('full_name'),
            'action' => $variables->getStringOrThrow('action'),
            'method' => strtolower($variables->getStringOrThrow('method')),
            'inputs' => $inputs,
        ];
        $this->componentName = 'GOV:Form';
    }
}

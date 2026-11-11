<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Validator\Constraints\Expression;

abstract readonly class AbstractRepeatedInput extends AbstractInput
{
    public AbstractInput $primary;
    public AbstractInput $repeat;

    /**
     * @param class-string $symfonyType
     */
    protected function __construct(
        string $symfonyType,
        string $name,
        string $label,
        ?LegendRecipe $legend,
        ?HintRecipe $hint,
        bool $required,
        bool $disabled,
        ?Constraints $constraints,
        public string $repeatLabel,
        public string $repeatError,
    ) {
        parent::__construct(
            $symfonyType,
            $name,
            $label,
            $legend,
            $hint,
            $required,
            $disabled,
            $constraints,
        );
        $this->primary = $this->makeChild($this->name, $this->label, $this->constraints);
        $this->repeat = $this->makeChild("{$this->name}_repeat_", $this->repeatLabel, new Constraints(
            new Expression(expression: "this.getParent().get('{$this->name}').getData() === this.getData()", message: $this->repeatError)
        ));
    }

    final public function render(ValidatingArray $variables): RenderableInterface
    {
        return $this->primary->render($variables);
    }

    final public function renderRepeat(ValidatingArray $variables): RenderableInterface
    {
        return $this->repeat->render($variables);
    }

    abstract protected function makeChild(string $name, string $label, ?Constraints $constraints): AbstractInput;
}

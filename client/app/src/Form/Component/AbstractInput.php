<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;

abstract readonly class AbstractInput
{
    protected LabelRecipe $label;
    protected ?LegendRecipe $legend;
    protected ?HintRecipe $hint;

    protected function __construct(
        /**
         * @var class-string $symfonyType
         */
        public string $symfonyType,
        public string $name,
        string|LabelRecipe $label,
        string|LegendRecipe|null $legend,
        string|HintRecipe|null $hint,
        public bool $required,
        public bool $disabled,
        public ?Constraints $constraints,
    ) {
        $this->legend = is_string($legend) ? new LegendRecipe($legend) : $legend;
        $this->label = is_string($label) ? new LabelRecipe($label) : $label;
        $this->hint = is_string($hint) ? new HintRecipe($hint) : $hint;
    }

    abstract public function render(ValidatingArray $variables): RenderableInterface;

    /**
     * @return array<string, string>
     */
    final public static function extractErrorMessages(ValidatingArray $variables): array
    {
        $iterator = $variables->getObjectOrNull('errors', FormErrorIterator::class);
        if (!$iterator instanceof \Traversable) {
            return [];
        }

        $errors = [];
        $iterator = new \RecursiveIteratorIterator($iterator);
        /**
         * @var FormError $error
         */
        foreach ($iterator as $error) {
            $errors[$variables->getStringOrThrow('id')] = $error->getMessage();
        }
        return $errors;
    }
}

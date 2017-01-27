<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 */
class Chain extends Constraint
{
    public $constraints;
    public $stopOnError = true;

    /**
     * {@inheritdoc}
     */
    public function __construct($options = null)
    {
        // no known options set? $options is the constraints array
        if (is_array($options) && !array_intersect(array_keys($options), ['groups', 'constraints', 'stopOnError'])) {
            $options = ['constraints' => $options];
        }

        parent::__construct($options);

        if (!is_array($this->constraints)) {
            throw new ConstraintDefinitionException('The option "constraints" is expected to be an array in constraint '.__CLASS__.'.');
        }

        foreach ($this->constraints as $constraint) {
            if (!$constraint instanceof Constraint) {
                throw new ConstraintDefinitionException('The value '.$constraint.' is not an instance of Constraint in constraint '.__CLASS__.'.');
            }

            if ($constraint instanceof Valid) {
                throw new ConstraintDefinitionException('The constraint Valid cannot be nested inside constraint '.__CLASS__.'.');
            }
        }
    }

    public function getRequiredOptions()
    {
        return ['constraints'];
    }
}

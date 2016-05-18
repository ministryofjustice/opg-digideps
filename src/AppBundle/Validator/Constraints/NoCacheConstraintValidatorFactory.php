<?php

namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

class NoCacheConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var ConstraintValidatorFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $nonCachableClasses;

    public function __construct(ConstraintValidatorFactoryInterface $factory, array $nonCachableClasses)
    {
        $this->factory = $factory;
        $this->nonCachableClasses = $nonCachableClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if (in_array($className, $this->nonCachableClasses, true)) {
            return new $className();
        }

        return $this->factory->getInstance($constraint);
    }
}

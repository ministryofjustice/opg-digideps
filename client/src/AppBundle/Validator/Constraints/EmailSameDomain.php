<?php
namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/** @Annotation */
class EmailSameDomain extends Constraint
{
    /**
     * @var string error message
     */
    public $message = 'Email domains do not match';

    /**
     * @var array validation groups
     */
    public $groups = [];

    /**
     * EmailSameDomain constructor.
     *
     * @param mixed|null $options
     */
    public function __construct($options)
    {
        $requiredOptions = ['message', 'groups'];

        foreach ($requiredOptions as $option) {
            if (isset($options[$option])) {
                $this->$option = $options[$option];
            } else {
                throw new MissingOptionsException("Missing option: '" . $option . "' required for constraint");
            }
        }

        parent::__construct();
    }

    public function validatedBy()
    {
        return 'email_same_domain';
    }

    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}

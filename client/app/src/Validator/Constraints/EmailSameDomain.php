<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

#[\Attribute]
class EmailSameDomain extends Constraint
{
    public string $message = 'Email domains do not match';

    public $groups = [];

    public function __construct(array $options = [])
    {
        if (!(isset($options['message']) && isset($options['groups']))) {
            throw new MissingOptionsException("Missing option(s): 'message' and 'groups' required for constraint", []);
        }

        $this->message = $options['message'];

        /** @var string[] $groups */
        $groups = $options['groups'];

        $this->groups = $groups;

        parent::__construct();
    }

    public function validatedBy(): string
    {
        return 'email_same_domain';
    }

    public function getTargets(): array|string
    {
        return self::PROPERTY_CONSTRAINT;
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CommonPassword extends Constraint
{
    public string $message = 'Your password is too easy for someone to guess. Please choose a different password.';

    public function __construct(mixed $options = null, ?array $groups = null, mixed $payload = null, string $message = '')
    {
        parent::__construct($options, $groups, $payload);
        if (!empty($message)) {
            $this->message = $message;
        }
    }
}

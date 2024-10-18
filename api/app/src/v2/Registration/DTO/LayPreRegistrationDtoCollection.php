<?php

namespace App\v2\Registration\DTO;

class LayPreRegistrationDtoCollection extends \ArrayObject
{
    public function append($item): void
    {
        if (!$item instanceof LayPreRegistrationDto) {
            throw new \InvalidArgumentException(sprintf('Only items of type %s are allowed', LayPreRegistrationDto::class));
        }

        parent::append($item);
    }
}

<?php

namespace AppBundle\v2\Registration\DTO;

class LayDeputyshipDtoCollection extends \ArrayObject
{
    /**
     * {@inheritDoc}
     */
    public function append($item)
    {
        if (!$item instanceof LayDeputyshipDto) {
            throw new \InvalidArgumentException(sprintf(
                'Only items of type %s are allowed',
                LayDeputyshipDto::class
            ));
        }

        parent::append($item);
    }
}

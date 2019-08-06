<?php

namespace AppBundle\v2\Transformer;

use AppBundle\v2\DTO\OrganisationDto;

class OrganisationTransformer
{
    /**
     * @param OrganisationDto $dto
     * @return array
     */
    public function transform(OrganisationDto $dto)
    {
        return [
            'id' => $dto->getId(),
            'name' => $dto->getName(),
            'email_identifier' => $dto->getEmailIdentifier(),
            'is_activated' => $dto->isActivated()
        ];
    }
}

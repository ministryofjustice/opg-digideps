<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Assembler;

use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;

class CasRecToOrgDeputyshipDtoAssembler
{
    public function assembleFromArray(array $data)
    {
        return (new OrgDeputyshipDto())
            ->setEmail($data['Email'])
            ->setDeputyNumber($data['Deputy No'])
            ->setFirstname($data['Dep Forename'])
            ->setLastname($data['Dep Surname']);
    }
}

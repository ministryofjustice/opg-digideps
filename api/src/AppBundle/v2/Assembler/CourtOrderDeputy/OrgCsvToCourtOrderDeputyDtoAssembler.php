<?php

namespace AppBundle\v2\Assembler\CourtOrderDeputy;

use AppBundle\v2\DTO\CourtOrderDeputyDto;

class OrgCsvToCourtOrderDeputyDtoAssembler
{
    public function assemble(array $data): CourtOrderDeputyDto {
        if (!$this->canAssemble($data)) {
            throw new \InvalidArgumentException('Cannot assemble CourtOrderDto: Missing expected data');
        }

        return (new CourtOrderDeputyDto())
            ->setDeputyNumber($data['Deputy No'])
            ->setFirstname($data['Forename'])
            ->setSurname($data['Surname'])
            ->setEmail($data['Email'] ?: null);
    }

    private function canAssemble(array $data): bool {
        return
            array_key_exists('Deputy No', $data) &&
            array_key_exists('Forename', $data) &&
            array_key_exists('Surname', $data);
    }
}

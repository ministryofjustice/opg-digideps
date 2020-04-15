<?php

namespace AppBundle\v2\Assembler\CourtOrderDeputy;

use AppBundle\Entity\User;
use AppBundle\v2\DTO\CourtOrderAddressDto;
use AppBundle\v2\DTO\CourtOrderDeputyDto;

class OrgCsvToCourtOrderDeputyDtoAssembler
{
    public function assemble(array $data): CourtOrderDeputyDto
    {
        if (!$this->canAssemble($data)) {
            throw new \InvalidArgumentException('Cannot assemble CourtOrderDto: Missing expected data');
        }

        return (new CourtOrderDeputyDto())
            ->setDeputyNumber(User::padDeputyNumber($data['Deputy No']))
            ->setFirstname($data['Dep Forename'])
            ->setSurname($data['Dep Surname'])
            ->setEmail($data['Email'] ?: null)
            ->setAddress($this->assembleAddress($data));
    }

    private function canAssemble(array $data): bool
    {
        return
            array_key_exists('Deputy No', $data) &&
            array_key_exists('Dep Forename', $data) &&
            array_key_exists('Dep Surname', $data);
    }

    private function assembleAddress(array $data): CourtOrderAddressDto
    {
        return (new CourtOrderAddressDto())
            ->setAddressLine1($data['Dep Adrs1'])
            ->setAddressLine2($data['Dep Adrs2'])
            ->setAddressLine3($data['Dep Adrs3'])
            ->setTown($data['Dep Adrs4'])
            ->setCounty($data['Dep Adrs5'])
            ->setPostcode($data['Dep Postcode']);
    }
}

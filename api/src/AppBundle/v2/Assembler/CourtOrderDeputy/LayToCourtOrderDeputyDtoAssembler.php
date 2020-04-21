<?php

namespace AppBundle\v2\Assembler\CourtOrderDeputy;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\User;
use AppBundle\v2\DTO\CourtOrderDeputyAddressDto;
use AppBundle\v2\DTO\CourtOrderDeputyDto;

class LayToCourtOrderDeputyDtoAssembler
{
    public function assemble(CasRec $casRec, User $user): CourtOrderDeputyDto
    {
        return (new CourtOrderDeputyDto())
            ->setDeputyNumber($casRec->getDeputyNo())
            ->setFirstname($user->getFirstname())
            ->setSurname($casRec->getDeputySurname())
            ->setEmail($user->getEmail())
            ->setAddress($this->assembleAddress($casRec, $user));
    }

    private function assembleAddress(CasRec $casRec, User $user): CourtOrderDeputyAddressDto
    {
        return (new CourtOrderDeputyAddressDto())
            ->setAddressLine1($user->getAddress1())
            ->setAddressLine2($user->getAddress2())
            ->setAddressLine3($user->getAddress3())
            ->setPostcode($casRec->getDeputyPostCode())
            ->setCountry($user->getAddressCountry());
    }
}

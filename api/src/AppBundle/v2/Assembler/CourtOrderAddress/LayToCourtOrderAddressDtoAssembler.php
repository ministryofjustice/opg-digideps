<?php

namespace AppBundle\v2\Assembler\CourtOrderAddress;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\User;
use AppBundle\v2\DTO\CourtOrderAddressDto;

class LayToCourtOrderAddressDtoAssembler
{
    public function assemble(CasRec $casRec, User $user): CourtOrderAddressDto {
        return (new CourtOrderAddressDto())
            ->setAddressLine1($user->getAddress1())
            ->setAddressLine2($user->getAddress2())
            ->setAddressLine3($user->getAddress3())
            ->setPostcode($casRec->getDeputyPostCode())
            ->setCountry($user->getAddressCountry());
    }
}

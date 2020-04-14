<?php

namespace AppBundle\v2\Assembler\CourtOrderDeputy;

use AppBundle\Entity\CasRec;
use AppBundle\Entity\User;
use AppBundle\v2\DTO\CourtOrderDeputyDto;

class LayToCourtOrderDeputyDtoAssembler
{
    public function assemble(CasRec $casRec, User $user): CourtOrderDeputyDto {
        return (new CourtOrderDeputyDto())
            ->setDeputyNumber($casRec->getDeputyNo())
            ->setFirstname($user->getFirstname())
            ->setSurname($casRec->getDeputySurname())
            ->setEmail($user->getEmail());
    }
}

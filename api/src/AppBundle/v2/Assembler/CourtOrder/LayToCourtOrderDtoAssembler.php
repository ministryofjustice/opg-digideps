<?php

namespace AppBundle\v2\Assembler\CourtOrder;

use AppBundle\Entity\CasRec;
use AppBundle\v2\DTO\CourtOrderDto;

class LayToCourtOrderDtoAssembler extends AbstractCourtOrderDtoAssembler
{
    /**
     * @param CasRec $casRec
     * @return CourtOrderDto
     */
    public function assemble(CasRec $casRec): CourtOrderDto
    {
        return (new CourtOrderDto())
            ->setCaseNumber($casRec->getCaseNumber())
            ->setOrderDate($casRec->getOrderDate())
            ->setType($this->determineType($casRec->getCorref()))
            ->setSupervisionLevel($this->determineSupervisionLevel($casRec->getTypeOfReport()));
    }
}

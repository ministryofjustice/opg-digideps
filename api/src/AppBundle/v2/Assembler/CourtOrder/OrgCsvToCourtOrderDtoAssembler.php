<?php

namespace AppBundle\v2\Assembler\CourtOrder;

use AppBundle\Entity\Client;
use AppBundle\v2\DTO\CourtOrderDto;

class OrgCsvToCourtOrderDtoAssembler extends AbstractCourtOrderDtoAssembler
{
    public function assemble(array $data)
    {
        if (!$this->canAssemble($data)) {
            throw new \InvalidArgumentException('Cannot assemble CourtOrderDto: Missing expected data');
        }

        return (new CourtOrderDto())
            ->setCaseNumber(Client::padCaseNumber(strtolower($data['Case'])))
            ->setOrderDate(new \DateTime($data['Made Date']))
            ->setType($this->determineType($data['Corref']))
            ->setSupervisionLevel($this->determineSupervisionLevel($data['Typeofrep']));
    }

    /**
     * @param array $data
     * @return bool
     */
    private function canAssemble(array $data): bool
    {
        return
            array_key_exists('Corref', $data) &&
            array_key_exists('Case', $data) &&
            array_key_exists('Made Date', $data) &&
            array_key_exists('Typeofrep', $data);
    }
}

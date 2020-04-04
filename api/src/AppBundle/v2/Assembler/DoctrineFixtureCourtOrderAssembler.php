<?php

namespace AppBundle\v2\Assembler;

use AppBundle\v2\DTO\ClientDto;
use AppBundle\v2\DTO\CourtOrderDto;
use AppBundle\v2\DTO\DtoPropertySetterTrait;

class DoctrineFixtureCourtOrderAssembler
{
    use DtoPropertySetterTrait;

    /**
     * @param array $data
     * @return CourtOrderDto
     * @throws \Exception
     */
    public function assembleFromArray(array $data)
    {
        $dto = new CourtOrderDto();
        $this->setPropertiesFromData($dto, $data, ['orderDate', 'client', 'reports']);

        $dto
            ->setOrderDate(new \DateTime($data['orderDate']))
            ->setClient($this->buildClientDto($data));

        return $dto;
    }

    /**
     * @param array $data
     * @return ClientDto
     * @throws \Exception
     */
    private function buildClientDto(array $data): ClientDto
    {
        return (new ClientDto())
            ->setFirstName($data['clientFirstName'])
            ->setLastName($data['clientLastName'])
            ->setEmail($data['clientEmail'])
            ->setPhone($data['clientPhone'])
            ->setDateOfBirth(new \DateTime($data['clientDob']))
            ->setAddress($data['clientAddress'])
            ->setAddress2($data['clientAddress2'])
            ->setCounty($data['clientCounty'])
            ->setPostcode($data['clientPostcode'])
            ->setCountry($data['clientCountry']);
    }
}

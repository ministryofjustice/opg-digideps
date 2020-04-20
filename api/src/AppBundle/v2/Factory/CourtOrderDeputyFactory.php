<?php

namespace AppBundle\v2\Factory;

use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\CourtOrderDeputyAddress;
use AppBundle\Entity\CourtOrderDeputy;
use AppBundle\Entity\Report\Report;
use AppBundle\v2\DTO\CourtOrderDeputyDto;
use AppBundle\v2\DTO\CourtOrderDto;

class CourtOrderDeputyFactory
{
    public function create(CourtOrderDeputyDto $deputyDto, CourtOrder $courtOrder): CourtOrderDeputy
    {
        $deputy = new CourtOrderDeputy();
        $deputy
            ->setDeputyNumber($deputyDto->getDeputyNumber())
            ->setFirstname($deputyDto->getFirstname())
            ->setSurname($deputyDto->getSurname())
            ->setEmail($deputyDto->getEmail());

        $addressDto = $deputyDto->getAddress();
        $address = new CourtOrderDeputyAddress();
        $address
            ->setAddressLine1($addressDto->getAddressLine1())
            ->setAddressLine2($addressDto->getAddressLine2())
            ->setAddressLine3($addressDto->getAddressLine3())
            ->setTown($addressDto->getTown())
            ->setCounty($addressDto->getCounty())
            ->setPostcode($addressDto->getPostcode())
            ->setCountry($addressDto->getCountry());

        $deputy->addAddress($address);
        $courtOrder->addDeputy($deputy);

        return $deputy;
    }
}

<?php

namespace AppBundle\v2\Factory;

use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\Report\Report;
use AppBundle\v2\DTO\CourtOrderDto;

class CourtOrderFactory
{
    /**
     * @param CourtOrderDto $dto
     * @param Client $client
     * @param Report $report
     * @return CourtOrder
     */
    public function create(CourtOrderDto $dto, Client $client, Report $report)
    {
        $courtOrder = new CourtOrder();
        $courtOrder
            ->setCaseNumber($dto->getCaseNumber())
            ->setOrderDate($dto->getOrderDate())
            ->setType($dto->getType())
            ->setSupervisionLevel($dto->getSupervisionLevel())
            ->setClient($client)
            ->addReport($report);

        $report->setCourtOrder($courtOrder);

        return $courtOrder;
    }
}

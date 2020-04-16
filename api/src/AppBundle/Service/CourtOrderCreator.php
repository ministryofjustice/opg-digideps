<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\CourtOrderDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Repository\CourtOrderRepository;
use AppBundle\v2\Assembler\CourtOrder\OrgCsvToCourtOrderDtoAssembler;
use AppBundle\v2\Assembler\CourtOrderDeputy\OrgCsvToCourtOrderDeputyDtoAssembler;
use AppBundle\v2\DTO\CourtOrderDeputyDto;
use AppBundle\v2\DTO\CourtOrderDto;
use AppBundle\v2\Factory\CourtOrderDeputyFactory;
use AppBundle\v2\Factory\CourtOrderFactory;
use Doctrine\ORM\EntityManagerInterface;

class CourtOrderCreator
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var CourtOrderRepository */
    private $courtOrderRepository;

    /** @var OrgCsvToCourtOrderDtoAssembler */
    private $courtOrderAssembler;

    /** @var OrgCsvToCourtOrderDeputyDtoAssembler */
    private $courtOrderDeputyAssembler;

    /** @var CourtOrderFactory */
    private $courtOrderFactory;

    /** @var CourtOrderDeputyFactory */
    private $courtOrderDeputyFactory;

    public function __construct(
        EntityManagerInterface $em,
        CourtOrderRepository $courtOrderRepository,
        OrgCsvToCourtOrderDtoAssembler $courtOrderAssembler,
        OrgCsvToCourtOrderDeputyDtoAssembler $courtOrderDeputyAssembler,
        CourtOrderFactory $courtOrderFactory,
        CourtOrderDeputyFactory $courtOrderDeputyFactory
    )
    {
        $this->em = $em;
        $this->courtOrderRepository = $courtOrderRepository;
        $this->courtOrderAssembler = $courtOrderAssembler;
        $this->courtOrderDeputyAssembler = $courtOrderDeputyAssembler;
        $this->courtOrderFactory = $courtOrderFactory;
        $this->courtOrderDeputyFactory = $courtOrderDeputyFactory;
    }

    /**
     * @param Client|null $client
     * @param string $courtOrderType
     * @return CourtOrder
     */
    private function getCourtOrder(?Client $client, string $courtOrderType): ?CourtOrder
    {
        $courtOrder = $this
            ->courtOrderRepository
            ->findOneBy([
                'caseNumber' => $client->getCaseNumber(),
                'type' => $courtOrderType
            ]);

        return $courtOrder;
    }

    /**
     * @param array $row
     * @param Client $client
     * @param Report $report
     */
    private function createCourtOrder(
        CourtOrderDto $courtOrderDto,
        CourtOrderDeputyDto $courtOrderDeputyDto,
        Client $client,
        Report $report,
        Organisation $organisation
    ): CourtOrder
    {
        $courtOrder = $this->courtOrderFactory->create($courtOrderDto, $client, $report);
        $deputy = $this->courtOrderDeputyFactory->create($courtOrderDeputyDto, $courtOrder);
        $deputy->setOrganisation($organisation);

        $this->em->persist($courtOrder);
        $this->em->flush();

        return $courtOrder;
    }

    private function updateCourtOrderDeputy(CourtOrderDeputy $deputy, CourtOrderDeputyDto $deputyDto, Organisation $organisation = null): void
    {
        $deputy
            ->setFirstname($deputyDto->getFirstname())
            ->setSurname($deputyDto->getSurname())
            ->setEmail($deputyDto->getEmail());

        $addressDto = $deputyDto->getAddress();
        $deputy->getAddresses()[0]
            ->setAddressLine1($addressDto->getAddressLine1())
            ->setAddressLine2($addressDto->getAddressLine2())
            ->setAddressLine3($addressDto->getAddressLine3())
            ->setTown($addressDto->getTown())
            ->setCounty($addressDto->getCounty())
            ->setPostcode($addressDto->getPostcode())
            ->setCountry($addressDto->getCountry());

        if (!is_null($organisation)) {
            $deputy->setOrganisation($organisation);
        }
    }

    public function upsertCourtOrder(CourtOrderDto $orderDto, CourtOrderDeputyDto $deputyDto, Report $report, Organisation $organisation): CourtOrder
    {
        $client = $report->getClient();

        $courtOrder = $this->getCourtOrder($client, $orderDto->getType());

        if (is_null($courtOrder)) {
            return $this->createCourtOrder($orderDto, $deputyDto, $client, $report, $organisation);
        }

        if ($this->courtOrderHasLayDeputy($courtOrder)) {
            throw new \RuntimeException('Case number already used by lay deputy');
        }

        if (count($courtOrder->getDeputies()) > 1) {
            throw new \RuntimeException('Court order has multiple organisations attached');
        }

        $deputy = $courtOrder->getDeputies()[0];

        // Deputy has changed, so generate a new court order
        if ($deputy->getDeputyNumber() !== $deputyDto->getDeputyNumber()) {
            $this->em->remove($courtOrder);
            $this->em->flush();

            return $this->createCourtOrder($orderDto, $deputyDto, $client, $report, $organisation);
        }

        $this->updateCourtOrderDeputy($deputy, $deputyDto, $organisation);
        $this->em->flush();

        return $courtOrder;
    }

    private function courtOrderHasLayDeputy(CourtOrder $courtOrder): bool
    {
        foreach ($courtOrder->getDeputies() as $deputy) {
            if (!is_null($deputy->getUser())) {
                return true;
            }
        }

        return false;
    }
}

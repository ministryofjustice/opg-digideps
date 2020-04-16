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

    private function getCourtOrder(Client $client, string $courtOrderType): ?CourtOrder
    {
        $courtOrder = $this
            ->courtOrderRepository
            ->findOneBy([
                'caseNumber' => $client->getCaseNumber(),
                'type' => $courtOrderType
            ]);

        return $courtOrder;
    }

    private function createCourtOrder(CourtOrderDto $courtOrderDto, Client $client, Report $report): CourtOrder
    {
        $courtOrder = $this->courtOrderFactory->create($courtOrderDto, $client, $report);

        $this->em->persist($courtOrder);
        $this->em->flush();

        return $courtOrder;
    }

    /**
     * Replace an existing court order with a dulpicate
     */
    private function recreateCourtOrder(CourtOrder $courtOrder): CourtOrder
    {
        $orderDto = (new CourtOrderDto())
            ->setCaseNumber($courtOrder->getCaseNumber())
            ->setType($courtOrder->getType())
            ->setSupervisionLevel($courtOrder->getSupervisionLevel())
            ->setOrderDate($courtOrder->getOrderDate());
        $client = $courtOrder->getClient();
        $report = $courtOrder->getReports()[0];

        $this->em->remove($courtOrder);
        $this->em->flush();

        return $this->createCourtOrder($orderDto, $client, $report);
    }

    private function createCourtOrderDeputy(CourtOrderDeputyDto $deputyDto, CourtOrder $courtOrder, Organisation $organisation): CourtOrderDeputy
    {
        $deputy = $this->courtOrderDeputyFactory->create($deputyDto, $courtOrder);
        $deputy->setOrganisation($organisation);

        $this->em->persist($deputy);
        $this->em->flush();

        return $deputy;
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

    public function upsertCourtOrder(CourtOrderDto $orderDto, Report $report): CourtOrder
    {
        $client = $report->getClient();

        $courtOrder = $this->getCourtOrder($client, $orderDto->getType());

        if (is_null($courtOrder)) {
            return $this->createCourtOrder($orderDto, $client, $report);
        } else {
            $courtOrder->setOrderDate($orderDto->getOrderDate());
            return $courtOrder;
        }
    }

    public function upsertCourtOrderDeputy(CourtOrderDeputyDto $deputyDto, CourtOrder $courtOrder, Organisation $organisation): CourtOrderDeputy
    {
        if ($this->courtOrderHasLayDeputy($courtOrder)) {
            throw new \RuntimeException('Case number already used by lay deputy');
        }

        if (count($courtOrder->getDeputies()) > 1) {
            throw new \RuntimeException('Court order has multiple organisations attached');
        }

        if (count($courtOrder->getDeputies()) === 0) {
            return $this->createCourtOrderDeputy($deputyDto, $courtOrder, $organisation);
        }

        $deputy = $courtOrder->getDeputies()[0];

        // Deputy has changed, so generate a new court order
        if ($deputy->getDeputyNumber() !== $deputyDto->getDeputyNumber()) {
            $newCourtOrder = $this->recreateCourtOrder($courtOrder);
            return $this->createCourtOrderDeputy($deputyDto, $newCourtOrder, $organisation);
        } else {
            $this->updateCourtOrderDeputy($deputy, $deputyDto, $organisation);
            $this->em->flush();

            return $deputy;
        }

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

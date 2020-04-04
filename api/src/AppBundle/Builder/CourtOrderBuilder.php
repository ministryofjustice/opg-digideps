<?php

namespace AppBundle\Builder;

use AppBundle\Entity\Client;
use AppBundle\Entity\CourtOrder;
use AppBundle\Entity\User;
use AppBundle\Service\DataNormaliser;
use AppBundle\v2\DTO\CourtOrderDto;
use Doctrine\ORM\EntityManagerInterface;

class CourtOrderBuilder
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var CourtOrder */
    private $item;

    /** @var CourtOrderDto */
    private $dto;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param CourtOrderDto $dto
     * @return CourtOrderBuilder
     * @throws \Exception
     */
    public function createItem(CourtOrderDto $dto): CourtOrderBuilder
    {
        $this->dto = $dto;

        $this->item = (new CourtOrder())
            ->setCaseNumber(DataNormaliser::normaliseCaseNumber($dto->getCaseNumber()))
            ->setType(strtoupper($dto->getType()))
            ->setOrderDate($dto->getOrderDate());

        if ($this->item->getType() === CourtOrder::SUBTYPE_PFA) {
            $this->item->setSupervisionLevel(strtoupper($dto->getSupervisionLevel()));
        }

        $this->em->persist($this->item);

        return $this;
    }

    /**
     * @return CourtOrderBuilder
     */
    public function addCourtOrderDeputies(): CourtOrderBuilder
    {
        // Currently no-op.
        return $this;
    }

    /**
     * @return CourtOrderBuilder
     * @throws \Doctrine\ORM\ORMException
     */
    public function addClient(): CourtOrderBuilder
    {
        $clientDto = $this->dto->getClient();
        $client = (new Client())
            ->setCaseNumber(User::padDeputyNumber($this->dto->getCaseNumber()))
            ->setFirstname($clientDto->getFirstName())
            ->setLastname($clientDto->getLastName())
            ->setAddress($clientDto->getAddress())
            ->setAddress2($clientDto->getAddress2())
            ->setCounty($clientDto->getCounty())
            ->setPostcode($clientDto->getPostcode())
            ->setCountry($clientDto->getCountry())
            ->setPhone($clientDto->getPhone())
            ->setEmail($clientDto->getEmail())
            ->setDateOfBirth($clientDto->getDateOfBirth());

        $this->em->persist($client);
        $this->item->setClient($client);

        return $this;
    }

    public function addReport(): CourtOrderBuilder
    {
        return $this;
    }

    /**
     * @return CourtOrderBuilder
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function persistItem(): CourtOrderBuilder
    {
        $this->em->flush();
        return $this;
    }

    /**
     * @return CourtOrder
     */
    public function getItem(): CourtOrder
    {
        return $this->item;
    }
}

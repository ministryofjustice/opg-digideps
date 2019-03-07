<?php

namespace AppBundle\v2\Repository;

use AppBundle\v2\Assembler\DeputyAssembler;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeputyRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ObjectRepository */
    private $deputyRepository;

    /**@var ClientRepository */
    private $clientRepository;

    /** @var DeputyAssembler */
    private $deputyAssembler;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ObjectRepository $deputyRepository
     * @param ClientRepository $clientRepository
     * @param DeputyAssembler $deputyAssembler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ObjectRepository $deputyRepository,
        ClientRepository $clientRepository,
        DeputyAssembler $deputyAssembler
    ) {
        $this->entityManager = $entityManager;
        $this->deputyRepository = $deputyRepository;
        $this->clientRepository = $clientRepository;
        $this->deputyAssembler = $deputyAssembler;
    }

    /**
     * @param $deputyId
     * @return \AppBundle\v2\DTO\DeputyDto
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDtoById($deputyId)
    {
        $dtoData = $this->getDtoDataArray($deputyId);

        return $this->deputyAssembler->assembleFromArray($dtoData);
    }

    /**
     * @param $id
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getDtoDataArray($id)
    {
        $sql = <<<QUERY
            SELECT id,firstname,lastname,email,role_name,address_postcode,odr_enabled
            FROM dd_user
            WHERE id = :id
QUERY;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data =  $stmt->fetch();
        $data['clients'] = $this->clientRepository->getDtoDataArrayByDeputy($id);

        return $data;
    }
}

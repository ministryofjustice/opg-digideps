<?php

namespace AppBundle\v2\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class ClientRepository
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**@var ObjectRepository */
    private $clientRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ClientRepository $clientRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ObjectRepository $clientRepository
    ) {
        $this->entityManager = $entityManager;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param $deputyId
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getDtoDataArrayByDeputy($deputyId)
    {
        $sql = <<<QUERY
          SELECT c.id,c.firstname,c.lastname,c.email,c.case_number,count(report.id) as report_count,odr.id as ndr_id
          FROM client c 
          JOIN deputy_case on deputy_case.client_id = c.id
          LEFT JOIN odr on odr.client_id = c.id
          LEFT JOIN report on report.client_id = c.id
          WHERE deputy_case.user_id = :deputyId
          GROUP BY c.id, odr.id
          ORDER BY c.id
QUERY;

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute(['deputyId' => $deputyId]);

        return $stmt->fetchAll();
    }
}

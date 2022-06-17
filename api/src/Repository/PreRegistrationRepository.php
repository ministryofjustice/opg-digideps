<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PreRegistration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class PreRegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PreRegistration::class);
    }

    public function deleteAll()
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('App\Entity\PreRegistration', 'p');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countAllEntities()
    {
        return $this
            ->getEntityManager()
            ->createQuery('SELECT COUNT(p.id) FROM App\Entity\PreRegistration p')
            ->getSingleScalarResult();
    }

    public function findByRegistrationDetails(string $caseNumber, string $clientLastname, string $deputySurname)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(PreRegistration::class, 'p')
            ->where('LOWER(p.caseNumber) = LOWER(:caseNumber)')
            ->andWhere('LOWER(p.clientLastname) = LOWER(:clientLastname)')
            ->andWhere('LOWER(p.deputySurname) = LOWER(:deputySurname)')
            ->setParameters(['caseNumber' => $caseNumber, 'clientLastname' => $clientLastname, 'deputySurname' => $deputySurname])
            ->getQuery()
            ->getResult();
    }

    public function findByCaseNumber(string $caseNumber)
    {
        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from(PreRegistration::class, 'p')
            ->where('LOWER(p.caseNumber) = LOWER(:caseNumber)')
            ->setParameters(['caseNumber' => $caseNumber])
            ->getQuery()
            ->getResult();
    }
}

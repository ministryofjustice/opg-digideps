<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Ndr\ClientBenefitsCheck;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NdrClientBenefitsCheckRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClientBenefitsCheck::class);
    }

    public function persistAndFlush(ClientBenefitsCheck $clientBenefitsCheck)
    {
        $this->_em->persist($clientBenefitsCheck);
        $this->_em->flush();
    }
}

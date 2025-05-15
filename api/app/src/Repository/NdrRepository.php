<?php

namespace App\Repository;

use App\Entity\Ndr\Debt;
use App\Entity\Ndr\Ndr;
use App\Entity\Ndr\OneOff;
use App\Entity\Ndr\StateBenefit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NdrRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ndr::class);
    }

    /**
     * add empty Debts to Ndr.
     * Called from doctrine listener.
     *
     * @return int changed records
     */
    public function addDebtsToNdrIfMissing(Ndr $ndr)
    {
        $ret = 0;

        // skips if already added
        if (count($ndr->getDebts()) > 0) {
            return $ret;
        }

        foreach (Debt::$debtTypeIds as $row) {
            $debt = new Debt($ndr, $row[0], $row[1], null);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * Called from doctrine listener.
     *
     * @return int changed records
     */
    public function addIncomeBenefitsToNdrIfMissing(Ndr $ndr)
    {
        $ret = 0;

        if (0 === count($ndr->getStateBenefits())) {
            foreach (StateBenefit::$stateBenefitsKeys as $typeId => $hasMoreDetails) {
                $incomeBenefit = new StateBenefit($ndr, $typeId, $hasMoreDetails);
                $this->_em->persist($incomeBenefit);
                $ndr->addStateBenefits($incomeBenefit);
                ++$ret;
            }
        }

        if (0 === count($ndr->getOneOff())) {
            foreach (OneOff::$oneOffKeys as $typeId => $hasMoreDetails) {
                $incomeBenefit = new OneOff($ndr, $typeId, $hasMoreDetails);
                $this->_em->persist($incomeBenefit);
                $ndr->addOneOff($incomeBenefit);
                ++$ret;
            }
        }

        return $ret;
    }

    public function findNdrByClientId(int $clientId): ?array
    {
        $result = $this
            ->getEntityManager()
            ->createQuery('SELECT n.id FROM App\Entity\Ndr\Ndr n JOIN n.client c WHERE c.id = :clientId')
            ->setParameter('clientId', $clientId)
            ->getResult();

        return empty($result) ? null : (array) $result;
    }
}

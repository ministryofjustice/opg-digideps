<?php

namespace AppBundle\Entity\Ndr;

use Doctrine\ORM\EntityRepository;

/**
 * NdrRepository.
 */
class NdrRepository extends EntityRepository
{
    /**
     * add empty Debts to Ndr.
     * Called from doctrine listener.
     *
     * @param Ndr $ndr
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
     * @param Ndr $ndr
     *
     * @return int changed records
     */
    public function addIncomeBenefitsToNdrIfMissing(Ndr $ndr)
    {
        $ret = 0;

        if (count($ndr->getStateBenefits()) === 0) {
            foreach (StateBenefit::$stateBenefitsKeys as $typeId => $hasMoreDetails) {
                $incomeBenefit = new StateBenefit($ndr, $typeId, $hasMoreDetails);
                $this->_em->persist($incomeBenefit);
                $ndr->addStateBenefits($incomeBenefit);
                ++$ret;
            }
        }

        if (count($ndr->getOneOff()) === 0) {
            foreach (OneOff::$oneOffKeys as $typeId => $hasMoreDetails) {
                $incomeBenefit = new OneOff($ndr, $typeId, $hasMoreDetails);
                $this->_em->persist($incomeBenefit);
                $ndr->addOneOff($incomeBenefit);
                ++$ret;
            }
        }

        return $ret;
    }
}

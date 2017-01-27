<?php

namespace AppBundle\Entity\Odr;

use Doctrine\ORM\EntityRepository;

/**
 * OdrRepository.
 */
class OdrRepository extends EntityRepository
{
    /**
     * add empty Debts to Odr.
     * Called from doctrine listener.
     *
     * @param Odr $odr
     *
     * @return int changed records
     */
    public function addDebtsToOdrIfMissing(Odr $odr)
    {
        $ret = 0;

        // skips if already added
        if (count($odr->getDebts()) > 0) {
            return $ret;
        }

        foreach (Debt::$debtTypeIds as $row) {
            $debt = new Debt($odr, $row[0], $row[1], null);
            $this->_em->persist($debt);
            ++$ret;
        }

        return $ret;
    }

    /**
     * Called from doctrine listener.
     *
     * @param Odr $odr
     *
     * @return int changed records
     */
    public function addIncomeBenefitsToOdrIfMissing(Odr $odr)
    {
        $ret = 0;

        if (count($odr->getStateBenefits()) === 0) {
            foreach (StateBenefit::$stateBenefitsKeys as $typeId => $hasMoreDetails) {
                $incomeBenefit = new StateBenefit($odr, $typeId, $hasMoreDetails);
                $this->_em->persist($incomeBenefit);
                $odr->addStateBenefits($incomeBenefit);
                ++$ret;
            }
        }

        if (count($odr->getOneOff()) === 0) {
            foreach (OneOff::$oneOffKeys as $typeId => $hasMoreDetails) {
                $incomeBenefit = new OneOff($odr, $typeId, $hasMoreDetails);
                $this->_em->persist($incomeBenefit);
                $odr->addOneOff($incomeBenefit);
                ++$ret;
            }
        }

        return $ret;
    }
}

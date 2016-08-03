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
     * add empty IncomeOneOff to Odr.
     * Called from doctrine listener.
     *
     * @param Odr $odr
     *
     * @return int changed records
     */
    public function addIncomeOneOffToOdrIfMissing(Odr $odr)
    {
        $ret = 0;

        // skips if already added
        if (count($odr->getIncomeOneOff()) > 0) {
            return $ret;
        }

        foreach (IncomeOneOff::$ids as $id) {
            $incomeOneOff = new IncomeOneOff($odr, $id, null);
            $this->_em->persist($incomeOneOff);
            ++$ret;
        }

        return $ret;
    }


}

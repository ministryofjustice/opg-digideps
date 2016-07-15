<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity as EntityDir;
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
    public function addDebtsToReportIfMissing(Odr $odr)
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


}

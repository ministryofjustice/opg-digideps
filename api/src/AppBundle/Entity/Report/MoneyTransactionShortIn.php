<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MoneyTransactionShortIn extends MoneyTransactionShort
{
    public function getType()
    {
        return 'in';
    }
}

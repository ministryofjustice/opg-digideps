<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class MoneyTransactionShortOut extends MoneyTransactionShort
{
    public function getType()
    {
        return 'out';
    }
}

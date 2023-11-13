<?php

namespace App\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class ProfServiceFeeCurrent extends ProfServiceFee
{
    public function getFeeTypeId()
    {
        return 'current';
    }
}

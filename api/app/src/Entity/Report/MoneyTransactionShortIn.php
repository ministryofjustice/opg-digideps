<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MoneyTransactionShortIn extends MoneyTransactionShort
{
    public function getType()
    {
        return 'in';
    }
}

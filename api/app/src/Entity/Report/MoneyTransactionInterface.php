<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

interface MoneyTransactionInterface
{
    /**
     * @return string in/out
     */
    public function getType();

    /**
     * @return float
     */
    public function getAmount();
}

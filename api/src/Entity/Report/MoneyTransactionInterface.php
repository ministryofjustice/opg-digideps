<?php

namespace App\Entity\Report;

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

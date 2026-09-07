<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

interface MoneyTransactionInterface
{
    /**
     * in/out
     */
    public function getType(): string;

    public function getAmount(): ?string;
}

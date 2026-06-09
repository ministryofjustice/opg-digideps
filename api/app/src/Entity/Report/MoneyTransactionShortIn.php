<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\HasLifecycleCallbacks]
class MoneyTransactionShortIn extends MoneyTransactionShort
{
    public function getType()
    {
        return 'in';
    }

    #[ORM\PrePersist]
    public function onPrePersist(PrePersistEventArgs $_): void
    {
        $this->getReport()->setMoneyTransactionsShortInExist('yes');
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if (
            $this->getReport()->getMoneyTransactionsShortIn()->filter(
                fn (MoneyTransactionShortIn $transaction, int $_) => $transaction->getId() !== $this->getId()
            )->isEmpty()
        ) {
            $this->getReport()->setMoneyTransactionsShortInExist('no');
        }
    }
}

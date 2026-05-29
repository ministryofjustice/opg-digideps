<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\HasLifecycleCallbacks]
class MoneyTransactionShortOut extends MoneyTransactionShort
{
    public function getType()
    {
        return 'out';
    }

    #[ORM\PrePersist]
    public function prePersist(PrePersistEventArgs $_): void
    {
        $this->getReport()->setMoneyTransactionsShortOutExist('yes');
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getMoneyTransactionsShortOut()->filter(
            fn(MoneyTransactionShortOut $transaction, int $_) => $transaction->getId() !== $this->getId()
        )->isEmpty()) {
            $this->getReport()->setMoneyTransactionsShortOutExist('no');
        }
    }
}

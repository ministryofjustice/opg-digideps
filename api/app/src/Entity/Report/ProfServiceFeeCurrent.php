<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity, ORM\HasLifecycleCallbacks]
class ProfServiceFeeCurrent extends ProfServiceFee
{
    public function getFeeTypeId()
    {
        return 'current';
    }

    #[ORM\PreRemove]
    public function onPreRemove(PreRemoveEventArgs $_): void
    {
        if ($this->getReport()->getCurrentProfServiceFees()->count() === 1) {
            $this->getReport()->setMoneyTransactionsShortInExist('no');
            $this->getReport()->setCurrentProfPaymentsReceived(null);
            $this->getReport()->setPreviousProfFeesEstimateGiven(null);
            $this->getReport()->setProfFeesEstimateSccoReason(null);
        }
    }
}

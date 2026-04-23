<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use Doctrine\ORM\Mapping as ORM;
use OPG\Digideps\Backend\Entity\Report\BankAccount;
use JMS\Serializer\Annotation as JMS;

trait HasBankAccountTrait
{
    /**
     * @var BankAccount
     */
    #[JMS\Groups(['account'])]
    #[JMS\SerializedName('bankAccount')]
    #[JMS\Type(BankAccount::class)]
    #[ORM\ManyToOne(targetEntity: BankAccount::class)]
    #[ORM\JoinColumn(name: 'bank_account_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private $bankAccount;

    /**
     * @return mixed
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @return $this Report
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;

        return $this;
    }
}

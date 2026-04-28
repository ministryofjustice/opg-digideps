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
    #[ORM\JoinColumn(name: 'bank_account_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[JMS\Type('OPG\Digideps\Backend\Entity\Report\BankAccount')]
    #[ORM\ManyToOne(targetEntity: BankAccount::class)]
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

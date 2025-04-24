<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\BankAccount;
use JMS\Serializer\Annotation as JMS;

trait HasBankAccountTrait
{
    /**
     * @var BankAccount
     *
     *
     *
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Report\BankAccount")
     *
     * @ORM\JoinColumn(name="bank_account_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    #[JMS\Groups(['account'])]
    #[JMS\SerializedName('bankAccount')]
    #[JMS\Type('App\Entity\Report\BankAccount')]
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

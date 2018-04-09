<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;

trait HasBankAccountTrait
{
    /**
     * @var AppBundle\Entity\Report\BankAccount
     * @JMS\SerializedName("bankAccount")
     * @JMS\Type("AppBundle\Entity\Report\BankAccount")
     *
     * @JMS\Groups({"gifts-account", "money-in-account", "money-out-account"})
     */
    private $bankAccount;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"gifts-account"})
     *
     * @Assert\NotBlank(message="transfer.accountTo.notBlank", groups={"money-transfer-account-to"})
     */
    private $bankAccountId;

    /**
     * @return mixed
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @param $bankAccount
     * @return $this
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBankAccountId()
    {
        return $this->bankAccountId;
    }

    /**
     * @param $bankAccountId
     * @return $this
     */
    public function setBankAccountId($bankAccountId)
    {
        $this->bankAccountId = $bankAccountId;
        return $this;
    }
}

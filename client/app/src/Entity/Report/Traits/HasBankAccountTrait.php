<?php

namespace App\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;

trait HasBankAccountTrait
{
    /**
     * @var App\Entity\Report\BankAccount
     * @JMS\SerializedName("bankAccount")
     * @JMS\Type("App\Entity\Report\BankAccount")
     *
     * @JMS\Groups({"account"})
     */
    private $bankAccount;

    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"account"})
     **/
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

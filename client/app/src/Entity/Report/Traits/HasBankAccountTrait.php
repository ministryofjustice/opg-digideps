<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\BankAccount;

trait HasBankAccountTrait
{
    /**
     * @var BankAccount
     * @JMS\SerializedName("bankAccount")
     * @JMS\Type("OPG\Digideps\Frontend\Entity\Report\BankAccount")
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

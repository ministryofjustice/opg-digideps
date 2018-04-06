<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;

trait HasBankAccountTrait
{
    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"gifts"})
     */
    private $bankAccount;

    /**
     * @return mixed
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * @param mixed $accountFromId
     * @return $this
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
        return $this;
    }
}

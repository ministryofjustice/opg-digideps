<?php

namespace AppBundle\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;

trait HasFromBankAccountTrait
{
    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"gifts"})
     */
    private $fromAccount;

    /**
     * @return mixed
     */
    public function getFromAccount()
    {
        return $this->fromAccount;
    }

    /**
     * @param mixed $accountFromId
     * @return $this
     */
    public function setFromAccount($fromAccount)
    {
        $this->fromAccount = $fromAccount;
        return $this;
    }
}

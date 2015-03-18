<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

class AccountTransaction
{
    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"money_in", "money_out"})
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"money_in", "money_out"})
     * @var string
     */
    private $amount;
    
    
    public function __construct($id, $amount)
    {
        $this->id = $id;
        $this->amount = $amount;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

}
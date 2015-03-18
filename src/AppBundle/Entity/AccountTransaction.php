<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

class AccountTransaction
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactions"})
     * @var integer
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactions"})
     * @var string
     */
    private $amount;
    
     /**
     * @JMS\Type("boolean")
     * @JMS\Groups({"transactions"})
     * @var string
     */
    private $hasMoreDetails;
    
     /**
     * @JMS\Type("string")
     * @JMS\Groups({"transactions"})
     * @var string
     */
    private $moreDetails;
    
    
    public function __construct($id, $amount, $hasMoreDetails = false)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->hasMoreDetails = $hasMoreDetails;
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
    
    public function getHasMoreDetails()
    {
      return $this->hasMoreDetails;
    }

    public function setHasMoreDetails($hasMoreDetails)
    {
      $this->hasMoreDetails = $hasMoreDetails;
    }
    
    public function getMoreDetails()
    {
      return $this->moreDetails;
    }

    public function setMoreDetails($moreDetails)
    {
      $this->moreDetails = $moreDetails;
    }

}
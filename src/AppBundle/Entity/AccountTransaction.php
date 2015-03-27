<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Assert\Callback(methods={"moreDetailsValidate"}, groups={"transactions"})
 */
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
     * @Assert\Type(type="numeric", message="account.moneyInOut.account.notNumeric", groups={"transactions"})
     * @var string
     */
    private $amount;
    
     /**
     * @JMS\Type("string")
     * @var string
     */
    private $type;
    
     /**
     * @JMS\Type("boolean")
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

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
    
    /**
     * @return boolean
     */
    public function hasMoreDetails()
    {
      return $this->hasMoreDetails;
    }

    /**
     * @param boolean $hasMoreDetails
     */
    public function setHasMoreDetails($hasMoreDetails)
    {
      $this->hasMoreDetails = $hasMoreDetails;
    }
    
    /**
     * @return string
     */
    public function getMoreDetails()
    {
       return $this->moreDetails;
    }

    /**
     * @param string $moreDetails
     */
    public function setMoreDetails($moreDetails)
    {
      $this->moreDetails = $moreDetails;
    }
    
    /**
     * Add error if the transaction needs more details, an amount is give, but not details are provided
     * @param ExecutionContextInterface $context
     */
    public function moreDetailsValidate(ExecutionContextInterface $context)
    {
        $moreDetailsClean = trim($this->getMoreDetails(), " \n");
        if ($this->hasMoreDetails() && $this->getAmount() > 0.0  && !$moreDetailsClean){
            $context->addViolationAt('moreDetails', 'account.moneyInOut.moreDetails.empty');
        }
    }

}
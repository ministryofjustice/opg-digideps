<?php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Account
{
    /**
     * @JMS\Type("integer")
     * @var integer $id
     */
    private $id;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * 
     * @var string $bank
     */
    private $bank;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\NotBlank( message="Sort code cannot be blank")
     * @Assert\Type(type="numeric")
     * @Assert\Length(max=6,min=6)
     * 
     * @var string $sortCode
     */
    private $sortCode;
    
    /**
     *
     * @JMS\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     * @Assert\Length(max=4,min=4)
     * 
     * @var string $accountNumber
     */
    private $accountNumber;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank()
     * @Assert\Date()
     * @var type 
     */
    private $openingDate;
    
    /**
     * @JMS\Type("string")
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     * 
     * @var decimal
     */
    private $openingBalance;
    
    /**
     * @JMS\Type("DateTime")
     * @Assert\NotBlank()
     * @Assert\Date()
     * @var type 
     */
    private $lastEdit;
    
    /**
     * @JMS\Type("integer")
     */
    private $report;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }
    
    public function getBank()
    {
        return $this->bank;
    }
    
    public function setSortCode($sortCode)
    {
        $this->sortCode = $sortCode;
        return $this;
    }
    
    public function getSortCode()
    {
        return $this->sortCode;
    }
    
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }
    
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
    
    public function setOpeningDate($openingDate)
    {
        $this->openingDate = $openingDate;
    }
    
    public function getOpeningDate()
    {
        return $this->openingDate;
    }
    
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
        return $this;
    }
    
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }
    
    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;
        return $this;
    }
    
    public function getLastEdit()
    {
        return $this->lastEdit;
    }
    
    public function getReport()
    {
        return $this->report;
    }
    
    
    public function setReport($report)
    {
        $this->report = $report;
        return $this;
    }
}
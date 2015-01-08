<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExpenditurePayments
 *
 * @ORM\Table(name="expenditure_payment")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExpenditurePaymentRepository")
 */
class ExpenditurePayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expenditure_payment_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Expenditure", inversedBy="expenditurePayments")
     * @ORM\JoinColumn(name="expenditure_id", referencedColumnName="id", onDelete="CASCADE" )
     */
    private $expenditure;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=14, scale=2, nullable=true)
     */
    private $amount;
	
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=200, nullable=true)
     */
    private $title;
	
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    private $description;
	
    /**
     * @var integer
     *
     * @ORM\Column(name="multiplier", type="integer", nullable=true)
     */
    private $multiplier;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="transaction_date", type="datetime", nullable=true)
     */
    private $transactionDate;
	
    
    public function getAmountMultiplied()
    {
        $amount = $this->amount;
        
        if(!empty($this->multiplier) && ($this->multiplier > 0)){
            $amount *= $this->multiplier;
        }
        return $amount;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return ExpenditurePayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return ExpenditurePayment
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ExpenditurePayment
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set multiplier
     *
     * @param integer $multiplier
     * @return ExpenditurePayment
     */
    public function setMultiplier($multiplier)
    {
        $this->multiplier = $multiplier;

        return $this;
    }

    /**
     * Get multiplier
     *
     * @return integer 
     */
    public function getMultiplier()
    {
        return $this->multiplier;
    }

    /**
     * Set transactionDate
     *
     * @param \DateTime $transactionDate
     * @return ExpenditurePayment
     */
    public function setTransactionDate($transactionDate)
    {
        $this->transactionDate = $transactionDate;

        return $this;
    }

    /**
     * Get transactionDate
     *
     * @return \DateTime 
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * Set expenditure
     *
     * @param \AppBundle\Entity\Expenditure $expenditure
     * @return ExpenditurePayment
     */
    public function setExpenditure(\AppBundle\Entity\Expenditure $expenditure = null)
    {
        $this->expenditure = $expenditure;

        return $this;
    }

    /**
     * Get expenditure
     *
     * @return \AppBundle\Entity\Expenditure 
     */
    public function getExpenditure()
    {
        return $this->expenditure;
    }
}

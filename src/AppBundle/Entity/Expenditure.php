<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Expenditures
 *
 * @ORM\Table(name="expenditure")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExpenditureRepository")
 */
class Expenditure implements TransactionInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expenditure_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account", inversedBy="expenditures")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $account;
	
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ExpenditureType", inversedBy="expenditures")
     * @ORM\JoinColumn(name="expenditure_type_id", referencedColumnName="id")
     */
    private $expenditureType;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ExpenditurePayment", mappedBy="expenditure", cascade={"persist"})
     */
    private $expenditurePayments;
	
    /**
     * Get total all payment associated to this expenditure
     * 
     * @return int
     */
    public function getTotal()
    {
        $total = 0;
        
        if(empty($this->expenditurePayments)){
            return $total;
        }
         
        foreach($this->expenditurePayments as $payment){
            $total += $payment->getAmountMultiplied();
        }
        return $total;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->expenditurePayments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set account
     *
     * @param \AppBundle\Entity\Account $account
     * @return Expenditure
     */
    public function setAccount(\AppBundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \AppBundle\Entity\Account 
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set expenditureType
     *
     * @param \AppBundle\Entity\ExpenditureType $expenditureType
     * @return Expenditure
     */
    public function setExpenditureType(\AppBundle\Entity\ExpenditureType $expenditureType = null)
    {
        $this->expenditureType = $expenditureType;

        return $this;
    }

    /**
     * Get expenditureType
     *
     * @return \AppBundle\Entity\ExpenditureType 
     */
    public function getExpenditureType()
    {
        return $this->expenditureType;
    }

    /**
     * Add expenditurePayments
     *
     * @param \AppBundle\Entity\ExpenditurePayment $expenditurePayments
     * @return Expenditure
     */
    public function addExpenditurePayment(\AppBundle\Entity\ExpenditurePayment $expenditurePayments)
    {
        $this->expenditurePayments[] = $expenditurePayments;

        return $this;
    }

    /**
     * Remove expenditurePayments
     *
     * @param \AppBundle\Entity\ExpenditurePayment $expenditurePayments
     */
    public function removeExpenditurePayment(\AppBundle\Entity\ExpenditurePayment $expenditurePayments)
    {
        $this->expenditurePayments->removeElement($expenditurePayments);
    }

    /**
     * Get expenditurePayments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExpenditurePayments()
    {
        return $this->expenditurePayments;
    }
}

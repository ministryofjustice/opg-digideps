<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Benefits
 *
 * @ORM\Table(name="benefit")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BenefitRepository")
 */
class Benefit implements TransactionInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="benefit_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
	
     /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account", inversedBy="benefits")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $account;
	
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\BenefitType", inversedBy="benefits")
     * @ORM\JoinColumn(name="benefit_type_id", referencedColumnName="id")
     */
    private $benefitType;
    
    /**
     *
     * @var integer
     * 
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\BenefitPayment", mappedBy="benefit")
     */
    private $benefitPayments;
    
    
    public function getTotal()
    {
        $total = 0;
        
        if(empty($this->benefitPayments)){
            return $total;
        }
         
        foreach($this->benefitPayments as $payment){
            $total += $payment->getAmountMultiplied();
        }
        
        return $total;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->benefitPayments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Benefit
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
     * Set benefitType
     *
     * @param \AppBundle\Entity\BenefitType $benefitType
     * @return Benefit
     */
    public function setBenefitType(\AppBundle\Entity\BenefitType $benefitType = null)
    {
        $this->benefitType = $benefitType;

        return $this;
    }

    /**
     * Get benefitType
     *
     * @return \AppBundle\Entity\BenefitType 
     */
    public function getBenefitType()
    {
        return $this->benefitType;
    }

    /**
     * Add benefitPayments
     *
     * @param \AppBundle\Entity\BenefitPayment $benefitPayments
     * @return Benefit
     */
    public function addBenefitPayment(\AppBundle\Entity\BenefitPayment $benefitPayments)
    {
        $this->benefitPayments[] = $benefitPayments;

        return $this;
    }

    /**
     * Remove benefitPayments
     *
     * @param \AppBundle\Entity\BenefitPayment $benefitPayments
     */
    public function removeBenefitPayment(\AppBundle\Entity\BenefitPayment $benefitPayments)
    {
        $this->benefitPayments->removeElement($benefitPayments);
    }

    /**
     * Get benefitPayments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBenefitPayments()
    {
        return $this->benefitPayments;
    }
}

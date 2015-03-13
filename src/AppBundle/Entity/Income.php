<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Incomes
 *
 * @ORM\Table(name="income")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IncomeRepository")
 */
class Income implements TransactionInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="income_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
	  
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Account", inversedBy="incomes")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $account;
    
    /**
     * @ORM\OneToMany( targetEntity="AppBundle\Entity\IncomePayment", mappedBy="income", cascade={"persist"})
     */
    private $incomePayments;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\IncomeType", inversedBy="incomes")
     * @ORM\JoinColumn(name="income_type_id", referencedColumnName="id")
     */
    private $incomeType;
	
    
    public function getTotal()
    {
        $total = 0;
        
        if(empty($this->incomePayments)){
            return $total;
        }
         
        foreach($this->incomePayments as $payment){
            $total += $payment->getAmountMultiplied();
        }
        return $total;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->incomePayments = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Income
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
     * Add incomePayments
     *
     * @param \AppBundle\Entity\IncomePayment $incomePayments
     * @return Income
     */
    public function addIncomePayment(\AppBundle\Entity\IncomePayment $incomePayments)
    {
        $this->incomePayments[] = $incomePayments;

        return $this;
    }

    /**
     * Remove incomePayments
     *
     * @param \AppBundle\Entity\IncomePayment $incomePayments
     */
    public function removeIncomePayment(\AppBundle\Entity\IncomePayment $incomePayments)
    {
        $this->incomePayments->removeElement($incomePayments);
    }

    /**
     * Get incomePayments
     *
     * @return IncomePayment[]
     */
    public function getIncomePayments()
    {
        return $this->incomePayments;
    }

    /**
     * Set incomeType
     *
     * @param \AppBundle\Entity\IncomeType $incomeType
     * @return Income
     */
    public function setIncomeType(\AppBundle\Entity\IncomeType $incomeType = null)
    {
        $this->incomeType = $incomeType;

        return $this;
    }

    /**
     * Get incomeType
     *
     * @return \AppBundle\Entity\IncomeType 
     */
    public function getIncomeType()
    {
        return $this->incomeType;
    }
}

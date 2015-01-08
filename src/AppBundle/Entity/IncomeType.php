<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IncomeType
 *
 * @ORM\Table(name="income_type")
 * @ORM\Entity
 */
class IncomeType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="income_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Income", mappedBy="incomeTypes")
     */
    private $incomes;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60, nullable=true)
     */
    private $name;
	
    /**
     * @var string
     *
     * @ORM\Column(name="form_name", type="string", length=60, nullable=true)
     */
    private $form_name;
	
    /**
     * @var boolean
     *
     * @ORM\Column(name="payment_description_required", type="boolean", nullable=true)
     */
    private $payment_description_required;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->incomes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return IncomeType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set form_name
     *
     * @param string $formName
     * @return IncomeType
     */
    public function setFormName($formName)
    {
        $this->form_name = $formName;

        return $this;
    }

    /**
     * Get form_name
     *
     * @return string 
     */
    public function getFormName()
    {
        return $this->form_name;
    }

    /**
     * Set payment_description_required
     *
     * @param boolean $paymentDescriptionRequired
     * @return IncomeType
     */
    public function setPaymentDescriptionRequired($paymentDescriptionRequired)
    {
        $this->payment_description_required = $paymentDescriptionRequired;

        return $this;
    }

    /**
     * Get payment_description_required
     *
     * @return boolean 
     */
    public function getPaymentDescriptionRequired()
    {
        return $this->payment_description_required;
    }

    /**
     * Add incomes
     *
     * @param \AppBundle\Entity\Income $incomes
     * @return IncomeType
     */
    public function addIncome(\AppBundle\Entity\Income $incomes)
    {
        $this->incomes[] = $incomes;

        return $this;
    }

    /**
     * Remove incomes
     *
     * @param \AppBundle\Entity\Income $incomes
     */
    public function removeIncome(\AppBundle\Entity\Income $incomes)
    {
        $this->incomes->removeElement($incomes);
    }

    /**
     * Get incomes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIncomes()
    {
        return $this->incomes;
    }
}

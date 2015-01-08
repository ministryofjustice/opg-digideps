<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExpenditureType
 *
 * @ORM\Table(name="expenditure_type")
 * @ORM\Entity
 */
class ExpenditureType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="expenditure_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Expenditure", mappedBy="expenditureType", cascade={"persist"})
     */
    private $expenditures;

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
        $this->expenditures = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return ExpenditureType
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
     * @return ExpenditureType
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
     * @return ExpenditureType
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
     * Add expenditures
     *
     * @param \AppBundle\Entity\Expenditure $expenditures
     * @return ExpenditureType
     */
    public function addExpenditure(\AppBundle\Entity\Expenditure $expenditures)
    {
        $this->expenditures[] = $expenditures;

        return $this;
    }

    /**
     * Remove expenditures
     *
     * @param \AppBundle\Entity\Expenditure $expenditures
     */
    public function removeExpenditure(\AppBundle\Entity\Expenditure $expenditures)
    {
        $this->expenditures->removeElement($expenditures);
    }

    /**
     * Get expenditures
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getExpenditures()
    {
        return $this->expenditures;
    }
}

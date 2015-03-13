<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BenefitType
 *
 * @ORM\Table(name="benefit_type")
 * @ORM\Entity
 */
class BenefitType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="benefit_type_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Benefit", mappedBy="benefitType")
     */
    private $benefits;

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
        $this->benefits = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return BenefitType
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
     * @return BenefitType
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
     * @return BenefitType
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
     * Add benefits
     *
     * @param \AppBundle\Entity\Benefit $benefits
     * @return BenefitType
     */
    public function addBenefit(\AppBundle\Entity\Benefit $benefits)
    {
        $this->benefits[] = $benefits;

        return $this;
    }

    /**
     * Remove benefits
     *
     * @param \AppBundle\Entity\Benefit $benefits
     */
    public function removeBenefit(\AppBundle\Entity\Benefit $benefits)
    {
        $this->benefits->removeElement($benefits);
    }

    /**
     * Get benefits
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBenefits()
    {
        return $this->benefits;
    }
}

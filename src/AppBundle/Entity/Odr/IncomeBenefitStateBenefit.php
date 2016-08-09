<?php

namespace AppBundle\Entity\Odr;

use AppBundle\Entity\Traits\OdrIncomeBenefitSingleTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="odr_income_state_benefit")
 */
class IncomeBenefitStateBenefit
{
    use OdrIncomeBenefitSingleTrait;

    public static $stateBenefitsKeys = [
        'contributionsBasedAllowance' => false,
        'incomeSupportPensionGuaranteeCredit' => false,
        'incomerelatedEmploymentSupportAllowance' => false,
        'incomebasedJobSeekerAllowance' => false,
        'housingBenefit' => false,
        'universalCredit' => false,
        'severeDisablementAllowance' => false,
        'disabilityLivingAllowance' => false,
        'attendanceAllowance' => false,
        'prsonalIndependencePayment' => false,
        'workingChildTaxCredits' => false,
        'otherBenefits' => true,
    ];

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"odr-income-state-benefits"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="odr_state_benefits_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Odr
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Odr\Odr")
     * @ORM\JoinColumn(name="odr_id", referencedColumnName="id")
     */
    private $odr;

}

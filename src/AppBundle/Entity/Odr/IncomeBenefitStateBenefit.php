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
        'contributions_based_allowance' => false,
        'income_support_pension_guarantee_credit' => false,
        'income_related_employment_support_allowance' => false,
        'income_based_job_seeker_allowance' => false,
        'housing_benefit' => false,
        'universal_credit' => false,
        'severe_disablement_allowance' => false,
        'disability_living_allowance' => false,
        'attendance_allowance' => false,
        'personal_independence_payment' => false,
        'working_child_tax_credits' => false,
        'other_benefits' => true,
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

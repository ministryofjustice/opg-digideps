<?php

namespace AppBundle\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="money_short_category")
 */
class MoneyShortCategory
{
    /**
     * @param $type in|out
     *
     * @return array
     */
    public static function getCategories($type)
    {
        return [
            'in'  => [
                // typeId => options
                'state_pension_and_benefit'               => [],
                'bequests'                                => [],
                'income_from_invesments_dividends_rental' => [],
                'sale_of_investments_property_assets'     => [],
                'salary_or_wages'                         => [],
                'compensations_and_damages_awards'        => [],
                'personal_pension'                        => [],
            ],
            'out' => [
                // typeId => options
                'accomodation_costs' => [],
                'care_fees' => [],
                'holidays' => [],
                'households_bills' => [],
                'personal_allowance' => [],
                'professional_fees' => [],
                'new_investments' => [],
                'travel_costs' => [],
            ]][$type];
    }

    /**
     * @var int
     *
     * @JMS\Type("integer")
     * @JMS\Groups({"money-short-categories-in", "money-short-categories-out"})
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="money_short_category_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Report
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Report\Report")
     * @ORM\JoinColumn(name="report_id", referencedColumnName="id")
     */
    private $report;

    /**
     * @var string
     * @JMS\Groups({"money-short-categories-in", "money-short-categories-out"})
     * @ORM\Column(name="type_id", type="string", nullable=false)
     */
    private $typeId;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     * @JMS\Groups({"money-short-categories-in", "money-short-categories-out"})
     * @ORM\Column(name="present", type="boolean", nullable=true)
     */
    private $present;

    /**
     * MoneyShortCategory constructor.
     *
     * @param Report $report
     * @param string $typeId
     * @param bool   $present
     */
    public function __construct(Report $report, $typeId, $present)
    {
        $this->report = $report;
        $this->typeId = $typeId;
        $this->present = $present;
    }

    /**
     * @return string in|out
     */
    public function getType()
    {
        foreach (['in', 'out'] as $type) {
            foreach (self::getCategories($type) as $typeId => $options) {
                if ($typeId == $this->typeId) {
                    return $type;
                }
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param Report $report
     */
    public function setReport($report)
    {
        $this->report = $report;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /**
     * @param string $typeId
     */
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPresent()
    {
        return $this->present;
    }

    /**
     * @param string $present
     */
    public function setPresent($present)
    {
        $this->present = $present;

        return $this;
    }
}

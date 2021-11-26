<?php

namespace App\Entity;

use App\Entity\Report\Report;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class CasRec
{
    const REALM_PA = 'REALM_PA';
    const REALM_PROF = 'REALM_PROF';
    const REALM_LAY = 'REALM_LAY';

    const CASREC_SOURCE = 'casrec';
    const SIRIUS_SOURCE = 'sirius';

    /**
     * Holds the mapping rules to define the report type based on the CSV file (CASREC)
     * Used by both PA and Lay.
     *
     * @var array
     */
    private static $csvToReportTypeMap = [
        // Lay
        [true, self::REALM_LAY, ['p3', 'p3g', 'l3', 'l3g'], 'opg103', Report::LAY_PFA_LOW_ASSETS_TYPE],
        // @deprecated (DDPB-2044)
        [true, self::REALM_LAY, ['l3', 'l3g', 'a3'], 'opg103', Report::LAY_PFA_LOW_ASSETS_TYPE],
        [true, self::REALM_LAY, ['p2', 'p2a', 'l2a', 'l2'], 'opg102', Report::LAY_PFA_HIGH_ASSETS_TYPE],
        // @deprecated (DDPB-2044)
        [true, self::REALM_LAY, ['l3', 'l3g', 'a3'], 'opg102', Report::LAY_PFA_HIGH_ASSETS_TYPE],
        [true, self::REALM_LAY, ['hw'], '', Report::LAY_HW_TYPE],
        [true, self::REALM_LAY, ['hw'], 'opg103', Report::LAY_COMBINED_LOW_ASSETS_TYPE],
        [true, self::REALM_LAY, ['hw'], 'opg102', Report::LAY_COMBINED_HIGH_ASSETS_TYPE],
        // PA
        [true, self::REALM_PA, ['a3'], 'opg103', Report::PA_PFA_LOW_ASSETS_TYPE],
        // @deprecated (DDPB-2044)
        [true, self::REALM_PA, ['l3', 'l3g', 'a3'], 'opg103', Report::PA_PFA_LOW_ASSETS_TYPE],
        [true, self::REALM_PA, ['a2', 'a2a'], 'opg102', Report::PA_PFA_HIGH_ASSETS_TYPE],
        // @deprecated (DDPB-2044)
        [true, self::REALM_PA, ['l3', 'l3g', 'a3'], 'opg102', Report::PA_PFA_HIGH_ASSETS_TYPE],
        [true, self::REALM_PA, ['hw'], '', Report::PA_HW_TYPE],
        [true, self::REALM_PA, ['hw'], 'opg103', Report::PA_COMBINED_LOW_ASSETS_TYPE],
        [true, self::REALM_PA, ['hw'], 'opg102', Report::PA_COMBINED_HIGH_ASSETS_TYPE],
        // Prof
        [true, self::REALM_PROF, ['p3', 'p3g'], 'opg103', Report::PROF_PFA_LOW_ASSETS_TYPE],
        // @deprecated (DDPB-2044)
        [true, self::REALM_PROF, ['l3', 'l3g', 'a3'], 'opg103', Report::PROF_PFA_LOW_ASSETS_TYPE],
        [true, self::REALM_PROF, ['p2', 'p2a'], 'opg102', Report::PROF_PFA_HIGH_ASSETS_TYPE],
        // @deprecated (DDPB-2044)
        [true, self::REALM_PROF, ['l3', 'l3g', 'a3'], 'opg102', Report::PROF_PFA_HIGH_ASSETS_TYPE],
        [true, self::REALM_PROF, ['hw'], '', Report::PROF_HW_TYPE],
        [true, self::REALM_PROF, ['hw'], 'opg103', Report::PROF_COMBINED_LOW_ASSETS],
        [true, self::REALM_PROF, ['hw'], 'opg102', Report::PROF_COMBINED_HIGH_ASSETS],
    ];

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $caseNumber;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $clientLastname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $deputyNo;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $deputySurname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Length(min=2, max=10, minMessage="postcode too short", maxMessage="postcode too long" )
     */
    private $deputyPostCode;

    /**
     * @var string OPG102|OPG103|empty string
     *
     * @JMS\Type("string")
     */
    private $typeOfReport;

    /**
     * @var string A2|C1|HW|L2|L2A|L3|L3G|P2A|PGA|PGC|S1A|S1N|empty
     *
     * typeOfReport=OPG103 only have
     *
     * @JMS\Type("string")
     */
    private $corref;

    /**
     * @JMS\Type("string")
     */
    private $otherColumns;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $source;

    /**
     * @var \DateTime
     */
    private $orderDate;

    public function __construct()
    {
    }

    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    public function getClientLastname()
    {
        return $this->clientLastname;
    }

    public function getDeputyNo()
    {
        return $this->deputyNo;
    }

    public function getDeputySurname()
    {
        return $this->deputySurname;
    }

    public function getDeputyPostCode()
    {
        return $this->deputyPostCode;
    }

    /**
     * @return string
     */
    public function getTypeOfReport()
    {
        return $this->typeOfReport;
    }

    /**
     * @return string
     */
    public function getCorref()
    {
        return $this->corref;
    }

    /**
     * @return array
     */
    public function getOtherColumns()
    {
        return unserialize($this->otherColumns) ?: [];
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    public function getColumn($key)
    {
        $row = unserialize($this->otherColumns) ?: [];

        return isset($row[$key]) ? $row[$key] : null;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return CasRec
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     *
     * @return CasRec
     */
    public function setSource($source)
    {
        $source = strtolower($source);
        if (!in_array($source, self::validSources())) {
            throw new \InvalidArgumentException(sprintf('Attempting to set invalid source: %s given', $source));
        }

        $this->source = $source;

        return $this;
    }

    /**
     * @return array
     */
    public static function validSources()
    {
        return [
            self::CASREC_SOURCE,
            self::SIRIUS_SOURCE,
        ];
    }

    /**
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * @return CasRec
     */
    public function setOrderDate(\DateTime $orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }
}

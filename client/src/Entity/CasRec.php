<?php

namespace App\Entity;

use App\Entity\Report\Report;
use DateTime;
use InvalidArgumentException;
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
        [true, self::REALM_PROF, ['hw'], 'opg103', Report::PROF_COMBINED_LOW_ASSETS_TYPE],
        [true, self::REALM_PROF, ['hw'], 'opg102', Report::PROF_COMBINED_HIGH_ASSETS_TYPE],
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
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     */
    private $updatedAt;

    /**
     * @var string
     */
    private $source;

    /**
     * @var DateTime
     */
    private $orderDate;

    public function __construct()
    {
    }

    public function getCaseNumber(): string
    {
        return $this->caseNumber;
    }

    public function getClientLastname(): string
    {
        return $this->clientLastname;
    }

    public function getDeputyNo(): string
    {
        return $this->deputyNo;
    }

    public function getDeputySurname(): string
    {
        return $this->deputySurname;
    }

    public function getDeputyPostCode(): string
    {
        return $this->deputyPostCode;
    }

    public function getTypeOfReport(): string
    {
        return $this->typeOfReport;
    }

    public function getCorref(): string
    {
        return $this->corref;
    }

    public function getOtherColumns(): array
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

    public function setUpdatedAt(DateTime $updatedAt): CasRec
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): CasRec
    {
        $source = strtolower($source);
        if (!in_array($source, self::validSources())) {
            throw new InvalidArgumentException(sprintf('Attempting to set invalid source: %s given', $source));
        }

        $this->source = $source;

        return $this;
    }

    public static function validSources(): array
    {
        return [
            self::CASREC_SOURCE,
            self::SIRIUS_SOURCE,
        ];
    }

    public function getOrderDate(): DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(DateTime $orderDate): CasRec
    {
        $this->orderDate = $orderDate;

        return $this;
    }
}

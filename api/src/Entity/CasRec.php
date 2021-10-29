<?php

namespace App\Entity;

use App\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="casrec", indexes={@ORM\Index(name="updated_at_idx", columns={"updated_at"})})
 * @ORM\Entity(repositoryClass="App\Repository\CasRecRepository")
 */
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
        [true, self::REALM_PROF, ['hw'], '', Report::TYPE_104_5],
        [true, self::REALM_PROF, ['hw'], 'opg103', Report::TYPE_103_4_5],
        [true, self::REALM_PROF, ['hw'], 'opg102', Report::TYPE_102_4_5],
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="casrec_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="client_case_number", type="string", length=20, nullable=false)
     */
    private $caseNumber;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="client_lastname", type="string", length=50, nullable=false)
     */
    private $clientLastname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="deputy_no", type="string", length=100, nullable=false)
     */
    private $deputyNo;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="deputy_lastname", type="string", length=100, nullable=true)
     *
     * @JMS\Type("string")
     */
    private $deputySurname;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_postcode", type="string", length=10, nullable=true)
     *
     * @Assert\Length(min=2, max=10, minMessage="postcode too short", maxMessage="postcode too long" )
     */
    private $deputyPostCode;

    /**
     * @var string OPG102|OPG103|empty string
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="type_of_report", type="string", length=10, nullable=true)
     */
    private $typeOfReport;

    /**
     * @var string A2|C1|HW|L2|L2A|L3|L3G|P2A|PGA|PGC|S1A|S1N|empty
     *
     * typeOfReport=OPG103 only have
     *
     * @JMS\Type("string")
     *
     * @ORM\Column(name="corref", type="string", length=10, nullable=true)
     */
    private $corref;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="other_columns", type="text", nullable=true)
     */
    private $otherColumns;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="uploaded_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", nullable=true, options={"default" : "casrec"})
     */
    private $source;

    /**
     * @var \DateTime
     * @ORM\Column(name="order_date", type="datetime", nullable=true)
     */
    private $orderDate;

    /**
     * Filled from cron.
     *
     * @var array
     *
     * @deprecated use App\Service\DataNormaliser
     */
    private static $normalizeChars = [
        'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
        'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
        'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U',
        'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
        'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i',
        'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u',
        'ú' => 'u', 'ü' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'ƒ' => 'f',
        'ă' => 'a', 'î' => 'i', 'â' => 'a', 'ș' => 's', 'ț' => 't', 'Ă' => 'A', 'Î' => 'I', 'Â' => 'A', 'Ș' => 'S', 'Ț' => 'T',
    ];

    public function __construct(array $row)
    {
        $this->caseNumber = self::normaliseCaseNumber($row['Case']);
        $this->clientLastname = self::normaliseSurname($row['Surname']);
        $this->deputyNo = self::normaliseDeputyNo($row['Deputy No']);
        $this->deputySurname = self::normaliseSurname($row['Dep Surname']);
        $this->deputyPostCode = self::normaliseSurname($row['Dep Postcode']);
        $this->typeOfReport = self::normaliseCorrefAndTypeOfRep($row['Typeofrep']);
        $this->corref = self::normaliseCorrefAndTypeOfRep($row['Corref']);
        $this->orderDate = $row['OrderDate'];

        $source = isset($row['Source']) ? $row['Source'] : self::CASREC_SOURCE;
        $this->setSource($source);

        $this->otherColumns = serialize($row);
        $this->createdAt = new \DateTime();
        $this->updatedAt = null;
    }

    private static function normaliseCorrefAndTypeOfRep($value)
    {
        return trim(strtolower($value));
    }

    /** @deprecated use App\Service\DataNormaliser */
    public static function normaliseSurname($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = strtr($value, self::$normalizeChars);
        // remove MBE suffix
        $value = preg_replace('/ (mbe|m b e)$/i', '', $value);
        // remove characters that are not a-z or 0-9 or spaces
        $value = preg_replace('/([^a-z0-9])/i', '', $value);

        return $value;
    }

    /** @deprecated use App\Service\DataNormaliser */
    public static function normaliseCaseNumber($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('#^([a-z0-9]+/)#i', '', $value);

        return $value;
    }

    /** @deprecated use App\Service\DataNormaliser */
    public static function normaliseDeputyNo($value)
    {
        $value = trim($value);
        $value = strtolower($value);

        return $value;
    }

    /** @deprecated use App\Service\DataNormaliser */
    public static function normalisePostCode($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        // remove MBE suffix
        $value = preg_replace('/ (mbe|m b e)$/i', '', $value);
        // remove characters that are not a-z or 0-9 or spaces
        $value = preg_replace('/([^a-z0-9])/i', '', $value);

        return $value;
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
     * Determine type of report based on 'Typeofrep' and 'Corref' columns in the Casrec CSV
     * 103: when corref = l3/l3g and typeofRep = opg103
     * 104: when corref == hw and typeofRep empty (104 CURRENTLY DISABLED)
     * 103: all the other cases;.
     *
     * @param string $typeOfRep e.g. opg103
     * @param string $corref    e.g. l3, l3g
     * @param string $realm     e.g. REALM_PROF
     *
     * @return string Report::TYPE_*
     */
    public static function getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref, $realm)
    {
        $typeOfRep = trim(strtolower($typeOfRep));
        $corref = trim(strtolower($corref));

        // find report type
        $reportType = null;
        foreach (self::$csvToReportTypeMap as $row) {
            list($enabled, $currentUserRole, $currentCorrefs, $currentTypeOfRep, $outputType) = $row;
            if ($enabled && $realm === $currentUserRole && in_array($corref, $currentCorrefs) && $typeOfRep === $currentTypeOfRep) {
                return $outputType;
            }
        }

        // default report type if no entry mached above
        switch ($realm) {
            case self::REALM_LAY:
                return Report::LAY_PFA_HIGH_ASSETS_TYPE;
            case self::REALM_PA:
                return Report::PA_PFA_HIGH_ASSETS_TYPE;
            case self::REALM_PROF:
                return Report::PROF_PFA_HIGH_ASSETS_TYPE;
        }

        throw new \Exception(__METHOD__.': realm not recognised to determine report type');
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

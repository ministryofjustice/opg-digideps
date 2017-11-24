<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Report\Report;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="casrec", indexes={@ORM\Index(name="updated_at_idx", columns={"updated_at"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Repository\CasRecRepository")
 */
class CasRec
{
    const STATS_FILE_PATH = '/tmp/dd_stats.csv';

    /**
     * Holds the mapping rules to define the report type based on the CSV file (CASREC)
     * Used by both PA and Lay
     *
     * @var array
     */
    private static $csvToReportTypeMap = [
        // Lay
        [true, User::ROLE_LAY_DEPUTY, ['l3', 'l3g', 'a3'], 'opg103', Report::TYPE_103],
        [true, User::ROLE_LAY_DEPUTY, ['l3', 'l3g', 'a3'], 'opg102', Report::TYPE_102],
        [Report::ENABLE_104, User::ROLE_LAY_DEPUTY, ['hw'], '', Report::TYPE_104],
        [Report::ENABLE_104_JOINT, User::ROLE_LAY_DEPUTY, ['hw'], 'opg103', Report::TYPE_103_4],
        [Report::ENABLE_104_JOINT, User::ROLE_LAY_DEPUTY, ['hw'], 'opg102', Report::TYPE_102_4],
        // PA
        [true, User::ROLE_PA, ['l3', 'l3g', 'a3'], 'opg103', Report::TYPE_103_6],
        [true, User::ROLE_PA, ['l3', 'l3g', 'a3'], 'opg102', Report::TYPE_102_6],
        [Report::ENABLE_104, User::ROLE_PA, ['hw'], '', Report::TYPE_104_6],
        [Report::ENABLE_104_JOINT, User::ROLE_PA, ['hw'], 'opg103', Report::TYPE_103_4_6],
        [Report::ENABLE_104_JOINT, User::ROLE_PA, ['hw'], 'opg102', Report::TYPE_102_4_6],
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
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    private $registrationDate;

    /**
     * Filled from cron
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="last_logged_in", type="datetime", nullable=true)
     */
    private $lastLoggedIn;

    /**
     * Filled from cron
     * @var int
     *
     * @JMS\Type("string")
     * @ORM\Column(name="reports_submitted", type="string", length=4, nullable=true)
     */
    private $nOfReportsSubmitted;

    /**
     * Filled from cron
     * @var \DateTime
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="last_report_submitted_at", type="datetime", nullable=true)
     */
    private $lastReportSubmittedAt;

    /**
     * Filled from cron
     * @var int
     *
     * @JMS\Type("string")
     * @ORM\Column(name="reports_active", type="string", length=4, nullable=true)
     */
    private $nOfReportsActive;

    /**
     * Filled from cron
     * @var array
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
        $this->deputyNo = self::normaliseDeputyNo( $row['Deputy No']);
        $this->deputySurname = self::normaliseSurname($row['Dep Surname']);
        $this->deputyPostCode = self::normaliseSurname($row['Dep Postcode']);
        $this->typeOfReport = self::normaliseCorrefAndTypeOfRep( $row['Typeofrep']);
        $this->corref = self::normaliseCorrefAndTypeOfRep($row['Corref']);

        $this->otherColumns = serialize($row);
        $this->createdAt = new \DateTime();
        $this->registrationDate = null;
        $this->updatedAt = null;
        $this->lastLoggedIn = null;
        $this->nOfReportsSubmitted = 'n.a.';
        $this->nOfReportsActive = 'n.a.';

    }

    private static function normaliseCorrefAndTypeOfRep($value)
    {
        return trim(strtolower($value));
    }

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

    public static function normaliseCaseNumber($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('#^([a-z0-9]+/)#i', '', $value);

        return $value;
    }

    public static function normaliseDeputyNo($value)
    {
        $value = trim($value);
        $value = strtolower($value);

        return $value;
    }

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
     * 103: all the other cases;
     *
     * @param string $typeOfRep e.g. opg103
     * @param string $corref e.g. l3, l3g
     * @param string $userRoleName e.g. ROLE_PA
     *
     * @return string Report::TYPE_*
     */
    public static function getTypeBasedOnTypeofRepAndCorref($typeOfRep, $corref, $userRoleName)
    {
        $typeOfRep = trim(strtolower($typeOfRep));
        $corref = trim(strtolower($corref));

        // find report type
        $reportType = null;
        foreach (self::$csvToReportTypeMap as $row) {
            list($enabled, $currentUserRole, $currentCorrefs, $currentTypeOfRep, $outputType) = $row;
            if ($enabled && $userRoleName === $currentUserRole && in_array($corref, $currentCorrefs) && $typeOfRep === $currentTypeOfRep) {
                return $outputType;
            }
        }

        // default report type if no entry mached above
        switch ($userRoleName) {
            case User::ROLE_LAY_DEPUTY:
                return Report::TYPE_102;
            case User::ROLE_PA:
                return Report::TYPE_102_6;
        }

        throw new \Exception(__METHOD__ . ": user role not recognised to determine report type");
    }

    /**
     * @return array
     */
    public function getOtherColumns()
    {
        return unserialize($this->otherColumns) ?: [];
    }

    public function toArray()
    {
        $dateFormat = function($date, $default) {
            return $date instanceof \DateTime ? $date->format('d/m/Y H:m') : $default;
        };

        return [
            "Uploaded at" => $dateFormat($this->createdAt, 'n.a.'),
            "Stats updated at" => $dateFormat($this->updatedAt, '-'),
            "Deputy registration date" => $dateFormat($this->registrationDate, 'n.a.'),
            "Deputy last logged in" => $dateFormat($this->lastLoggedIn, 'n.a.'),
            "Reports submitted" =>  $this->nOfReportsSubmitted ?: 'n.a.',
            "Last report submitted at" =>  $dateFormat($this->lastReportSubmittedAt, 'n.a.'),
            "Reports active" =>  $this->nOfReportsActive ?: 'n.a.'
        ] + $this->getOtherColumns();
    }

    /**
     * @param \DateTime $updatedAt
     * @return CasRec
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @param \DateTime $registrationDate
     * @return CasRec
     */
    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }

    /**
     * @param \DateTime $lastLoggedIn
     * @return CasRec
     */
    public function setLastLoggedIn(\DateTime $lastLoggedIn = null)
    {
        $this->lastLoggedIn = $lastLoggedIn;

        return $this;
    }

    /**
     * @param int $nOfReportsSubmitted
     * @return CasRec
     */
    public function setNOfReportsSubmitted($nOfReportsSubmitted)
    {
        $this->nOfReportsSubmitted = $nOfReportsSubmitted;

        return $this;
    }

    /**
     * @param \DateTime $lastReportSubmittedAt
     * @return CasRec
     */
    public function setLastReportSubmittedAt(\DateTime $lastReportSubmittedAt = null)
    {
        $this->lastReportSubmittedAt = $lastReportSubmittedAt;

        return $this;
    }

    /**
     * @param int $nOfReportsActive
     * @return CasRec
     */
    public function setNOfReportsActive($nOfReportsActive)
    {
        $this->nOfReportsActive = $nOfReportsActive;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Report\Report;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
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
        $this->caseNumber = $row['Case'] ?? '';
        $this->clientLastname = $row['ClientSurname'] ?? '';
        $this->deputyUid = $row['DeputyUid'] ?? '';
        $this->deputySurname = $row['DeputySurname'] ?? '';
        $this->deputyAddress1 = $row['DeputyAddress1'];
        $this->deputyAddress2 = $row['DeputyAddress2'];
        $this->deputyAddress3 = $row['DeputyAddress3'];
        $this->deputyAddress4 = $row['DeputyAddress4'];
        $this->deputyAddress5 = $row['DeputyAddress5'];
        $this->deputyPostCode = $row['Dep Postcode'];
        $this->typeOfReport = $row['ReportType'];
        $this->ndr = $row['NDR'];
        $this->orderDate = $row['MadeDate'];
        $this->orderType = $row['OrderType'];
        $this->isCoDeputy = $row['CoDeputy'];

        $this->createdAt = new DateTime();
        $this->updatedAt = null;
    }

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\SequenceGenerator(sequenceName="casrec_id_seq", allocationSize=1, initialValue=1)
     */
    private int $id;

    /**
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="client_case_number", type="string", length=20, nullable=false)
     */
    private string $caseNumber;

    /**
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="client_lastname", type="string", length=50, nullable=false)
     */
    private string $clientLastname;

    /**
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="deputy_uid", type="string", length=100, nullable=false)
     */
    private string $deputyUid;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="deputy_lastname", type="string", length=100, nullable=true)
     *
     * @JMS\Type("string")
     */
    private string $deputySurname;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_address_1", type="string", nullable=true)
     */
    private ?string $deputyAddress1;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_address_2", type="string", nullable=true)
     */
    private ?string $deputyAddress2;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_address_3", type="string", nullable=true)
     */
    private ?string $deputyAddress3;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_address_4", type="string", nullable=true)
     */
    private ?string $deputyAddress4;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_address_5", type="string", nullable=true)
     */
    private ?string $deputyAddress5;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="deputy_postcode", type="string", length=10, nullable=true)
     *
     * @Assert\Length(min=2, max=10, minMessage="postcode too short", maxMessage="postcode too long" )
     */
    private ?string $deputyPostCode;

    /**
     * @JMS\Type("string")
     *
     * @ORM\Column(name="type_of_report", type="string", length=10, nullable=true)
     */
    private ?string $typeOfReport;

    /**
     * @JMS\Type("bool")
     *
     * @ORM\Column(name="ndr", type="boolean", nullable=true)
     */
    private ?string $ndr;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="uploaded_at", type="datetime", nullable=true)
     */
    private ?DateTime $createdAt;

    /**
     * @JMS\Type("DateTime<'Y-m-d H:i:s'>")
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt;

    /**
     * @ORM\Column(name="order_date", type="datetime", nullable=true)
     */
    private ?DateTime $orderDate;

    /**
     * @ORM\Column(name="order_type", type="string", nullable=true)
     */
    private ?string $orderType;

    /**
     * @ORM\Column(name="is_co_deputy", type="boolean", nullable=true)
     */
    private ?bool $isCoDeputy;

    /** @deprecated use App\Service\DataNormaliser */
    public static function normaliseCaseNumber($value)
    {
        $value = trim($value);
        $value = strtolower($value);
        $value = preg_replace('#^([a-z0-9]+/)#i', '', $value);

        return $value;
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
    public static function normaliseDeputyNo($value)
    {
        $value = trim($value);
        $value = strtolower($value);

        return $value;
    }

    private static function normaliseCorrefAndTypeOfRep($value)
    {
        return trim(strtolower($value));
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

        throw new Exception(__METHOD__.': realm not recognised to determine report type');
    }

    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    public function getClientLastname()
    {
        return $this->clientLastname;
    }

    public function getDeputyUid()
    {
        return $this->deputyUid;
    }

    public function getDeputySurname(): string
    {
        return $this->deputySurname;
    }

    public function getDeputyPostCode(): ?string
    {
        return $this->deputyPostCode;
    }

    public function getTypeOfReport(): ?string
    {
        return $this->typeOfReport;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getOrderDate(): ?DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(DateTime $orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDeputyAddress1(): ?string
    {
        return $this->deputyAddress1;
    }

    public function setDeputyAddress1(?string $deputyAddress1): self
    {
        $this->deputyAddress1 = $deputyAddress1;

        return $this;
    }

    public function getDeputyAddress2(): ?string
    {
        return $this->deputyAddress2;
    }

    public function setDeputyAddress2(?string $deputyAddress2): self
    {
        $this->deputyAddress2 = $deputyAddress2;

        return $this;
    }

    public function getDeputyAddress3(): ?string
    {
        return $this->deputyAddress3;
    }

    public function setDeputyAddress3(?string $deputyAddress3): self
    {
        $this->deputyAddress3 = $deputyAddress3;

        return $this;
    }

    public function getDeputyAddress4(): ?string
    {
        return $this->deputyAddress4;
    }

    public function setDeputyAddress4(?string $deputyAddress4): self
    {
        $this->deputyAddress4 = $deputyAddress4;

        return $this;
    }

    public function getDeputyAddress5(): ?string
    {
        return $this->deputyAddress5;
    }

    public function setDeputyAddress5(?string $deputyAddress5): self
    {
        $this->deputyAddress5 = $deputyAddress5;

        return $this;
    }

    public function getNdr(): ?string
    {
        return $this->ndr;
    }

    public function setNdr(?string $ndr): self
    {
        $this->ndr = $ndr;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOrderType(): mixed
    {
        return $this->orderType;
    }

    public function setOrderType(mixed $orderType): self
    {
        $this->orderType = $orderType;

        return $this;
    }

    public function getIsCoDeputy(): mixed
    {
        return $this->isCoDeputy;
    }

    public function setIsCoDeputy(mixed $isCoDeputy): self
    {
        $this->isCoDeputy = $isCoDeputy;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Report\Report;
use App\Entity\Traits\CreateUpdateTimestamps;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="pre_registration", indexes={@ORM\Index(name="updated_at_idx", columns={"updated_at"})})
 *
 * @ORM\Entity(repositoryClass="App\Repository\PreRegistrationRepository")
 *
 * @ORM\HasLifecycleCallbacks()
 */
class PreRegistration
{
    use CreateUpdateTimestamps;

    public const REALM_PA = 'REALM_PA';
    public const REALM_PROF = 'REALM_PROF';
    public const REALM_LAY = 'REALM_LAY';

    public const SINGLE_TYPE = 'SINGLE';
    public const HYBRID_TYPE = 'HYBRID';
    public const DUAL_TYPE = 'DUAL';

    public function __construct(array $row)
    {
        $this->caseNumber = $row['Case'] ?? '';
        $this->clientLastname = $row['ClientSurname'] ?? '';
        $this->deputyUid = $row['DeputyUid'] ?? '';
        $this->deputyFirstname = $row['DeputyFirstname'] ?? '';
        $this->deputySurname = $row['DeputySurname'] ?? '';
        $this->deputyAddress1 = $row['DeputyAddress1'] ?? null;
        $this->deputyAddress2 = $row['DeputyAddress2'] ?? null;
        $this->deputyAddress3 = $row['DeputyAddress3'] ?? null;
        $this->deputyAddress4 = $row['DeputyAddress4'] ?? null;
        $this->deputyAddress5 = $row['DeputyAddress5'] ?? null;
        $this->deputyPostCode = $row['DeputyPostcode'] ?? null;
        $this->typeOfReport = $row['ReportType'] ?? null;
        $this->ndr = isset($row['NDR']) ? 'yes' === $row['NDR'] : null;
        $this->orderDate = isset($row['MadeDate']) ? new \DateTime($row['MadeDate']) : null;
        $this->orderType = $row['OrderType'] ?? null;
        $this->isCoDeputy = isset($row['CoDeputy']) ? 'yes' === $row['CoDeputy'] : null;
        $this->hybrid = $row['Hybrid'] ?? null;

        $this->updatedAt = null;
    }

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\SequenceGenerator(sequenceName="pre_registration_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="deputy_firstname", type="string", length=100, nullable=true)
     *
     * @JMS\Type("string")
     */
    private string $deputyFirstname;

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
    private ?bool $ndr;

    /**
     * @ORM\Column(name="order_date", type="datetime", nullable=true)
     */
    private ?\DateTime $orderDate;

    /**
     * @ORM\Column(name="order_type", type="string", nullable=true)
     */
    private ?string $orderType;

    /**
     * @ORM\Column(name="hybrid", type="string", nullable=true)
     */
    private ?string $hybrid;

    /**
     * @ORM\Column(name="is_co_deputy", type="boolean", nullable=true)
     */
    private ?bool $isCoDeputy;

    public static function getReportTypeByOrderType(string $reportType, string $orderType, string $realm): string
    {
        // drop opg from string
        $reportType = substr($reportType, 3);
        $orderType = trim(strtolower($orderType));

        if (Report::TYPE_HEALTH_WELFARE !== $reportType && 'hw' === $orderType) {
            $fullReportType = sprintf('%s-4', $reportType);
        } else {
            $fullReportType = $reportType;
        }

        $fullReportType = match ($realm) {
            self::REALM_LAY => $fullReportType,
            self::REALM_PA => sprintf('%s-6', $fullReportType),
            self::REALM_PROF => sprintf('%s-5', $fullReportType),
            default => throw new \Exception(__METHOD__.': realm not recognised to determine report type'),
        };

        if (!in_array($fullReportType, [...Report::getAllLayTypes(), ...Report::getAllPaTypes(), ...Report::getAllProfTypes()])) {
            $message = sprintf('Translated report type "%s" is not recognised', $fullReportType);
            throw new \UnexpectedValueException($message);
        }

        return $fullReportType;
    }

    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    public function setCaseNumber(string $caseNumber): self
    {
        $this->caseNumber = strtolower($caseNumber);

        return $this;
    }

    public function getClientLastname()
    {
        return $this->clientLastname;
    }

    public function getDeputyUid()
    {
        return $this->deputyUid;
    }

    public function getDeputyFirstname(): string
    {
        return $this->deputyFirstname;
    }

    public function setDeputyFirstname(string $deputyFirstname): self
    {
        $this->deputyFirstname = $deputyFirstname;

        return $this;
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

    public function getOrderDate(): ?\DateTime
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTime $orderDate)
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

    public function getNdr(): ?bool
    {
        return $this->ndr;
    }

    public function setNdr(?bool $ndr): self
    {
        $this->ndr = $ndr;

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

    public function getHybrid(): ?string
    {
        return $this->hybrid;
    }

    public function setHybrid(?string $hybrid): self
    {
        $this->hybrid = $hybrid;

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

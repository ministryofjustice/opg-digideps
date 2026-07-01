<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'mental_capacity')]
#[ORM\Entity]
class MentalCapacity
{
    public const string CAPACITY_CHANGED = 'changed';
    public const string CAPACITY_STAYED_SAME = 'stayedSame';

    #[JMS\Type('integer')]
    #[ORM\Column(name: 'id', type: 'integer', nullable: false)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'mental_capacity_id_seq', allocationSize: 1, initialValue: 1)]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'report_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'mentalCapacity', targetEntity: Report::class)]
    private Report $report;

    /**
     * changed | stayedSame (see constants)
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['mental-capacity'])]
    #[ORM\Column(name: 'has_capacity_changed', type: 'string', length: 25, nullable: true)]
    private ?string $hasCapacityChanged = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['mental-capacity'])]
    #[ORM\Column(name: 'has_capacity_changed_details', type: 'text', nullable: true)]
    private ?string $hasCapacityChangedDetails = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['mental-capacity'])]
    #[ORM\Column(name: 'mental_assessment_date', type: 'date', nullable: true)]
    private ?\DateTime $mentalAssessmentDate;

    public function __construct(Report $report)
    {
        $this->mentalAssessmentDate = null;
        $this->report = $report;
        $report->setMentalCapacity($this);
    }

    public function getId(): int
    {
        return $this->id ?? 0;
    }

    public function setId(int $id): static
    {
        if ($this->id === null) {
            $this->id = $id;
        } elseif ($id === 0) {
            throw new \DomainException('You may not set the id of an entity to zero.');
        } else {
            throw new \LogicException('You may not set the id of an entity more than once.');
        }

        return $this;
    }

    public function setReport(Report $report): static
    {
        $this->report = $report;

        return $this;
    }

    public function getReport(): Report
    {
        return $this->report;
    }

    public function getHasCapacityChanged(): ?string
    {
        return $this->hasCapacityChanged;
    }

    public function getHasCapacityChangedDetails(): ?string
    {
        return $this->hasCapacityChangedDetails;
    }

    public function setHasCapacityChanged(?string $hasCapacityChanged): static
    {
        $this->hasCapacityChanged = $hasCapacityChanged;

        return $this;
    }

    public function setHasCapacityChangedDetails(?string $hasCapacityChangedDetails): static
    {
        $this->hasCapacityChangedDetails = $hasCapacityChangedDetails;

        return $this;
    }

    public function getMentalAssessmentDate(): ?\DateTime
    {
        return $this->mentalAssessmentDate;
    }

    public function setMentalAssessmentDate(?\DateTime $mentalAssessmentDate): static
    {
        $this->mentalAssessmentDate = $mentalAssessmentDate;

        return $this;
    }

    public function cleanUpUnusedData(): void
    {
        if ($this->hasCapacityChanged == self::CAPACITY_STAYED_SAME) {
            $this->hasCapacityChangedDetails = null;
        }
    }
}

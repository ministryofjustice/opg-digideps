<?php

namespace OPG\Digideps\Frontend\Entity\Report;

use OPG\Digideps\Frontend\Entity\Report\Traits\HasReportTrait;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class MentalCapacity
{
    use HasReportTrait;

    public const string CAPACITY_CHANGED = 'changed';
    public const string CAPACITY_STAYED_SAME = 'stayedSame';

    #[JMS\Type('integer')]
    #[JMS\Groups(['mental-capacity'])]
    private int $id;

    #[JMS\Type('string')]
    #[JMS\Groups(['mental-capacity'])]
    #[Assert\NotBlank(message: 'mentalCapacity.hasCapacityChanged.notBlank', groups: ['capacity'])]
    private ?string $hasCapacityChanged = null;

    #[JMS\Type('string')]
    #[JMS\Groups(['mental-capacity'])]
    #[Assert\NotBlank(message: 'mentalCapacity.hasCapacityChangedDetails.notBlank', groups: ['has-capacity-changed-yes'])]
    private ?string $hasCapacityChangedDetails = null;

    #[JMS\Type("DateTime<'Y-m-d'>")]
    #[JMS\Groups(['mental-assessment-date'])]
    #[Assert\NotBlank(message: 'mentalCapacity.mentalAssessmentDate.notBlank', groups: ['mental-assessment-date'])]
    private ?\DateTime $mentalAssessmentDate = null;

    public function getId(): int
    {
        return $this->id;
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
}

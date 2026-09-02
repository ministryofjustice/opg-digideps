<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\UnsubmittedSection;

trait ReportUnsubmittedSections
{
    /**
     * @var array<UnsubmittedSection>
     */
    private array $unsubmittedSections = [];

    public function getUnsubmittedSections(): array
    {
        return $this->unsubmittedSections;
    }

    /**
     * @param array<UnsubmittedSection> $unsubmittedSections
     */
    public function setUnsubmittedSections(array $unsubmittedSections): static
    {
        $this->unsubmittedSections = $unsubmittedSections;

        return $this;
    }

    /**
     * @var ?string comma-separated list of section identifiers; see ReportType values
     */
    #[JMS\Type('string')]
    #[JMS\Groups(['report_unsubmitted_sections_list'])]
    private ?string $unsubmittedSectionsList = null;

    public function getUnsubmittedSectionsList(): ?string
    {
        return $this->unsubmittedSectionsList;
    }

    public function setUnsubmittedSectionsList(string $unsubmittedSectionsList): static
    {
        $this->unsubmittedSectionsList = $unsubmittedSectionsList;

        return $this;
    }
}

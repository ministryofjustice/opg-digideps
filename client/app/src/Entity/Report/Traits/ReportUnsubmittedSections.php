<?php

namespace OPG\Digideps\Frontend\Entity\Report\Traits;

use JMS\Serializer\Annotation as JMS;
use OPG\Digideps\Frontend\Entity\Report\UnsubmittedSection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportUnsubmittedSections
{
    /**
     * @var array<UnsubmittedSection>
     */
    private array $unsubmittedSections = [];

    /**
     * @return array<UnsubmittedSection>
     */
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

    public function setUnsubmittedSectionsList(?string $unsubmittedSectionsList): static
    {
        $this->unsubmittedSectionsList = $unsubmittedSectionsList;

        return $this;
    }

    /**
     * Used by Twig template rendering Report model
     */
    public function isSectionFlaggedForAttention($sectionId): bool
    {
        $unsubmittedSections = array_map('trim', array_filter(
            explode(',', $this->unsubmittedSectionsList ?? '')
        ));

        return in_array($sectionId, $unsubmittedSections);
    }

    /**
     * Used by Report model validation callback
     */
    public function unsubmittedSectionAtLeastOnce(ExecutionContextInterface $context): void
    {
        $incompleteSections = array_filter(
            $this->getUnsubmittedSections(),
            fn (UnsubmittedSection $section) => $section->present
        );

        if (count($incompleteSections) === 0) {
            // add error to all the sections as no section was marked as incomplete
            $context->buildViolation('report.unsubmissionSections.atLeastOnce')->atPath('unsubmittedSections[0].present')->addViolation();
            for ($i = 1, $count = count($this->getUnsubmittedSections()); $i < $count; ++$i) {
                $context->buildViolation('')->atPath("unsubmittedSections[$i].present")->addViolation();
            }
        }
    }
}

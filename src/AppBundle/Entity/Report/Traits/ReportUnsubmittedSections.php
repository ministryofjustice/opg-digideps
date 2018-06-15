<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\UnsubmittedSection;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait ReportUnsubmittedSections
{
    /**
     * @var UnsubmittedSection[]
     */
    private $unsubmittedSection = [];

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @JMS\Groups({"report_unsubmitted_sections_list"})
     */
    private $unsubmittedSectionsList;

    /**
     * @param UnsubmittedSection[] $unsubmittedSection
     */
    public function setUnsubmittedSection($unsubmittedSection)
    {
        $this->unsubmittedSection = $unsubmittedSection;
    }

    /**
     * Needed to fill form collection
     *
     * @return UnsubmittedSection[]
     */
    public function getUnsubmittedSection()
    {
        // init with available section if empty
        if (empty($this->unsubmittedSection)) {
            foreach ($this->getAvailableSections() as $sectionId) {
                $this->unsubmittedSection[] = new UnsubmittedSection($sectionId, false);
            }
        }

        return $this->unsubmittedSection;
    }

    /**
     * @return string
     */
    public function getUnsubmittedSectionsList()
    {
        return $this->unsubmittedSectionsList;
    }

    /**
     * @param string $unsubmittedSectionsList
     *
     * @return Report
     */
    public function setUnsubmittedSectionsList($unsubmittedSectionsList)
    {
        $this->unsubmittedSectionsList = $unsubmittedSectionsList;

        return $this;
    }

    /**
     * @return array of section IDs
     */
    public function getUnsubmittedSectionsIds()
    {
        return array_filter(array_map(function ($us) {
            return $us->isPresent() ? $us->getId() : null;
        }, $this->getUnsubmittedSection()));
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function unsubmittedSectionAtLeastOnce(ExecutionContextInterface $context)
    {
        if (empty($this->getUnsubmittedSectionsIds())) {
            // add error to all the sections
            $context->buildViolation('report.unsubmissionSections.atLeastOnce')->atPath('unsubmittedSection[0].present')->addViolation();
            for ($i = 1, $count = count($this->getUnsubmittedSection()); $i < $count; $i++) {
                $context->buildViolation('')->atPath("unsubmittedSection[$i].present")->addViolation();
            }
        }
    }
}

<?php

namespace AppBundle\Entity\Report\Traits;

use AppBundle\Entity\Report\UnsubmittedSection;
use Symfony\Component\Validator\ExecutionContextInterface;

trait ReportUnsubmittedSections
{
    /**
     * @var UnsubmittedSection[]
     */
    private $unsubmittedSection = [];

    /**
     * @param UnsubmittedSection[] $unsubmittedSection
     */
    public function setUnsubmittedSection($unsubmittedSection)
    {
        // TODO map into the model in order to read and use for next story
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

    public function getUnsubmittedSectionsIds()
    {
        return array_filter(array_map(function($us) {
            return $us->isPresent() ? $us->getId() : null;
        }, $this->getUnsubmittedSection()));
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function unsubmittedSectionAtLeastOnce(ExecutionContextInterface $context)
    {
        if (empty($this->getUnsubmittedSectionsIds())) {
            $context->addViolationAt('unsubmittedSection', 'report.unsubmissionSections.atLeastOnce');
        }
    }
}

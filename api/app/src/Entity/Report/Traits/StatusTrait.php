<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Report\Traits;

use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Service\ReportStatusService;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait StatusTrait
{
    /**
     * Holds a copy of result of the ReportStatusService results in the form
     * [sectionId => [state=>, nOfRecords=>] ]
     * returned by the ReportStatusService::get<section>State.
     *
     * Set by endpoints hooks on section CRUD operations that call `updateSectionsStatusCache` below
     *
     * Note: Manually Json-serialised. `json_array` type not working properly in the unity of work in this doctrine version
     */
    #[JMS\Exclude]
    #[ORM\Column(name: 'status_cached', type: 'text', nullable: true)]
    private ?string $sectionStatusesCached;

    /**
     * Holds a copy of result of the ReportStatusService::getStatus() results
     * Used for ORG dashboard for tab calculation and pagination
     *
     * value: STATUS_* constant
     */
    #[JMS\Exclude]
    #[ORM\Column(name: 'report_status_cached', type: 'string', length: 20, nullable: true)]
    private ?string $reportStatusCached = null;

    /**
     * Holds a copy of the [sectionId => [state=>, nOfRecords=>].
     */
    #[JMS\Exclude]
    public function getSectionStatusesCached(): array
    {
        return $this->sectionStatusesCached ? json_decode($this->sectionStatusesCached, true) : [];
    }

    public function setSectionStatusesCached(array $status): void
    {
        $this->sectionStatusesCached = json_encode($status) ?: throw new \ValueError("Could not encode data");
    }

    /**
     * //TODO remove adn check test passing.
     */
    public function getReportStatusCached(): ?string
    {
        return $this->reportStatusCached;
    }

    /**
     * Update the status cache of the given sections,
     * and also the report.reportStatusCached using the cache.
     *
     * using the `ReportService::getSectionStateNotCached`
     *
     * @param null|array<string> $sectionIds
     */
    public function updateSectionsStatusCache(?array $sectionIds = null): void
    {
        if (is_null($sectionIds)) {
            $sectionIds = $this->getAvailableSections();
        }

        $currentSectionStatus = $this->getSectionStatusesCached();

        $currentReportStatus = $this->getStatus();

        $sectionIds[] = Report::SECTION_MONEY_TRANSFERS;
        $sectionIds[] = Report::SECTION_BALANCE;

        foreach ($sectionIds as $sectionId) {
            if ($this->hasSection($sectionId)) {
                $currentSectionStatus[$sectionId] = $currentReportStatus->getSectionStateNotCached($sectionId);
            }
        }

        $this->setSectionStatusesCached($currentSectionStatus);

        // update report status, using the cached version of the section statuses
        // Note: the isDue is skipped
        $this->reportStatusCached = $currentReportStatus
            ->setUseStatusCache(true)
            ->getStatusIgnoringDueDate();
    }

    #[JMS\VirtualProperty]
    #[JMS\Groups(['status', 'report-status', 'decision-status', 'contact-status', 'visits-care-state', 'expenses-state', 'gifts-state', 'account-state', 'money-transfer-state', 'money-in-state', 'money-out-state', 'asset-state', 'debt-state', 'action-state', 'more-info-state', 'balance-state', 'money-in-short-state', 'money-out-short-state', 'fee-state', 'documents-state', 'lifestyle-state', 'client-benefits-check-state'])]
    public function getStatus(): ReportStatusService
    {
        return new ReportStatusService($this);
    }
}

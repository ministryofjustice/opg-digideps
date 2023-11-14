<?php

namespace App\Entity\Report\Traits;

use App\Entity\Report\Report;
use App\Service\ReportStatusService;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

trait StatusTrait
{
    /**
     * Holds a copy of result of the ReportStatusService results in the form
     * [sectionId => [state=>, nOfRecords=>] ]
     * returned by the ReportStatusSevice::get<section>State.
     *
     * Set by endpoints hooks on section CRUD operations that call `updateSectionsStatusCache` below
     *
     * Note: Manually Json-serialised. `json_array` type not working properly in the unity of work in this doctrine version
     *
     * @var string
     *
     * @JMS\Exclude()
     *
     * @ORM\Column(name="status_cached", type="text", nullable=true)
     */
    private $sectionStatusesCached;

    /**
     * @var string
     *
     * Holds a copy of result of the ReportStatusService::getStatus() results
     * Used for ORG dashboard for tab calculation and pagination
     *
     * value: STATUS_* constant
     *
     * @JMS\Exclude()
     *
     * @ORM\Column(name="report_status_cached", type="string", length=20, nullable=true)
     */
    private $reportStatusCached;

    /**
     * Holds a copy of the [sectionId => [state=>, nOfRecords=>].
     *
     * @JMS\Exclude
     *
     * @return array
     */
    public function getSectionStatusesCached()
    {
        return $this->sectionStatusesCached ? json_decode($this->sectionStatusesCached, true) : [];
    }

    public function setSectionStatusesCached(array $status)
    {
        $this->sectionStatusesCached = json_encode($status);
    }

    /**
     * //TODO remove adn check test passing.
     *
     * @return string
     */
    public function getReportStatusCached()
    {
        return $this->reportStatusCached;
    }

    /**
     * Update the status cache of the given sections,
     * and also the report.reportStatusCached using the cache.
     *
     * using the `ReportService::getSectionStateNotCached`
     */
    public function updateSectionsStatusCache(array $sectionIds)
    {
        $currentSectionStatus = $this->getSectionStatusesCached();

        foreach ($currentSectionStatus as $statusKey => $statusValues) {
            if (in_array($statusKey, $this->getExcludeSections())) {
                unset($currentSectionStatus[$statusKey]);
            }
        }

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
            ->getStatusIgnoringDueDate(true);
    }

    /**
     * @JMS\VirtualProperty
     *
     * @JMS\Groups({
     *     "status",
     *     "report-status",
     *     "decision-status",
     *     "contact-status",
     *     "visits-care-state",
     *     "expenses-state",
     *     "gifts-state",
     *     "account-state",
     *     "money-transfer-state",
     *     "money-in-state",
     *     "money-out-state",
     *     "asset-state",
     *     "debt-state",
     *     "action-state",
     *     "more-info-state",
     *     "balance-state",
     *     "money-in-short-state",
     *     "money-out-short-state",
     *     "fee-state",
     *     "documents-state",
     *     "lifestyle-state",
     *     "client-benefits-check-state",
     * })
     *
     * @return ReportStatusService
     */
    public function getStatus()
    {
        return new ReportStatusService($this);
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Sync\Model\Sirius;

/**
 * This class is only serialized.
 */
class SiriusReportPdfDocumentMetadata implements SiriusMetadataInterface
{
    public ?\DateTime $reportingPeriodFrom = null;
    public ?\DateTime $reportingPeriodTo = null;
    public ?\DateTime $dateSubmitted = null;
    public ?int $year = null;
    public ?int $submissionId = null;
    public string $type;

    // the digideps-derived resource type: 102-4, 103-6, 104-5, 103-4-5 etc.
    public ?string $digidepsReportType = null;

    /** @var string[] */
    public array $courtOrderUids = [];
}

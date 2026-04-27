<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Registration\Uploader;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Report\Report;

/**
 * If $reportTypeShouldChangeTo is set, this is the proposed new type for the found report.
 * If $activeClientExistsForCase is true, we found at least one active case with the same case number as the DTO,
 * but it had an incompatible report. This is always true if a compatible client and report were found, but not vice
 * versa.
 */
final readonly class ClientMatch
{
    public function __construct(
        public ?Client $client,
        public ?Report $report,
        public ?string $reportTypeShouldChangeTo,
        public bool $activeClientExistsForCase,
    ) {
    }
}

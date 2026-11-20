<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use OPG\Digideps\Backend\Entity\Report\Report;

/**
 * A pair of reports, where $replaceReport is the report which could be replaced,
 * and $withReport is the report which could replace it, for a client.
 *
 * This has to be calculated by looking at reports and court orders associated
 * with the client.
 */
class ReportReplacement implements \JsonSerializable
{
    public function __construct(
        public Report $replaceReport,
        public Report $withReport
    ) {
    }

    /**
     * @return array{replaceReport: int, withReport: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'replaceReport' => $this->replaceReport->getId(),
            'withReport' => $this->withReport->getId()
        ];
    }
}

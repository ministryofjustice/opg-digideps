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
class PossibleReportReplacement implements \JsonSerializable
{
    public function __construct(
        public Report $replaceReport,
        public Report $withReport
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'replaceReport' => $this->replaceReport->getId(),
            'withReport' => $this->withReport->getId()
        ];
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report;

use OPG\Digideps\Common\Validating\ValidatingArray;
use OPG\Digideps\Common\Validating\ValidationException;

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
        public int $replaceReport,
        public int $withReport
    ) {
    }

    /**
     * @return array{replaceReport: int, withReport: int}
     */
    public function jsonSerialize(): array
    {
        return [
            'replaceReport' => $this->replaceReport,
            'withReport' => $this->withReport
        ];
    }

    /**
     * @throws ValidationException
     */
    public static function jsonDeserialize(string $json): ReportReplacement
    {
        $data = new ValidatingArray((array)json_decode($json, true));
        return new ReportReplacement(
            $data->getIntegerOrThrow('replaceReport'),
            $data->getIntegerOrThrow('withReport')
        );
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

final readonly class Table
{
    public const string NUMERIC_FORMAT = ''; //Should be 'numeric' but that would be inconsistent with other tables currently

    /**
     * @var array<Row>
     */
    public array $rows;

    public function __construct(
        public ?string $caption,
        /**
         * @var array<Column>|null
         */
        public ?array $columns,
        public ?Header $header,
        Row ...$rows
    ) {
        $this->rows = $rows;
    }
}

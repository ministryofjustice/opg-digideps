<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class Table
{
    /**
     * @var array<Row>
     */
    public array $rows;

    public function __construct(
        public string|RenderableInterface|null $caption,
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

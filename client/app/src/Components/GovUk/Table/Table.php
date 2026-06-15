<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GovUk\Table;

final readonly class Table
{
    /**
     * @var array<Row>
     */
    public array $rows;

    public function __construct(public ?Row $header, Row ...$rows)
    {
        $this->rows = $rows;
    }
}

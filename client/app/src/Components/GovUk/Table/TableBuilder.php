<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GovUk\Table;

final class TableBuilder
{
    private ?Row $header;

    /**
     * @var array<Row>
     */
    private array $rows;

    public function __construct(
        private readonly bool $firstColumnIsHeader = false,
        private readonly bool $sized = true
    ) {
        $this->header = null;
        $this->rows = [];
    }

    public function addHeader(string|Cell ...$cells): TableBuilder
    {
        $cells = array_map(fn (string|Cell $cell): Cell => is_string($cell) ? new Cell($cell) : $cell, $cells);
        $this->header = new Row($this->sized, false, ...$cells);
        return $this;
    }

    public function addRow(string|Cell ...$cells): TableBuilder
    {
        $cells = array_map(fn (string|Cell $cell): Cell => is_string($cell) ? new Cell($cell) : $cell, $cells);
        $this->rows[] = new Row($this->header === null && $this->sized, $this->firstColumnIsHeader, ...$cells);
        return $this;
    }

    public function makeTable(): Table
    {
        return new Table($this->header, ...$this->rows);
    }
}

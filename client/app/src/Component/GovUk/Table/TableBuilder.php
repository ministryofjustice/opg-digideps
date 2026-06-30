<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\Table;

final class TableBuilder
{
    private ?Row $header;

    /**
     * @var array<Row>
     */
    private array $rows;

    /**
     * @var array<Column>|null
     */
    private ?array $columns;

    public function __construct(
        private readonly bool $firstColumnIsHeader = false,
        private readonly ?string $caption = null
    ) {
        $this->header = null;
        $this->rows = [];
        $this->columns = null;
    }

    /**
     * @return array<Cell>
     */
    private function mapCells(string|Cell ...$cells): array
    {
        return array_map(fn (string|Cell $cell): Cell => is_string($cell) ? new Cell($cell) : $cell, $cells);
    }

    public function addHeader(string|Cell ...$cells): TableBuilder
    {
        $this->header = new Row(false, true, ...$this->mapCells(...$cells));
        return $this;
    }

    public function addRow(string|Cell ...$cells): TableBuilder
    {
        $this->rows[] = new Row($this->firstColumnIsHeader, false, ...$this->mapCells(...$cells));
        return $this;
    }

    public function makeTable(): Table
    {
        return new Table($this->caption, $this->columns, $this->header, ...$this->rows);
    }

    public function addColumns(int ...$sizes): TableBuilder
    {
        $count = array_sum($sizes);
        if (count($sizes) !== 0 && $count <= 4) {
            $this->columns = array_map(fn (int $size) => new Column($this->getWidthAsFraction($size, $count)), $sizes);
        }
        return $this;
    }

    private function getWidthAsFraction(int $size, int $count): ?string
    {
        if ($size < 1 || $size > 3 || $count < 2 || $count > 4) {
            return null;
        }

        $dividend = match ($size) {
            1 => 'one',
            2 => 'two',
            3 => 'three'
        };
        $plural = $size === 1 ? '' : 's';
        $divisor = match ($count) {
            2 => 'half',
            3 => "third{$plural}",
            4 => "quarter{$plural}",
        };

        return $count === $size ? 'full' : "{$dividend}-{$divisor}";
    }
}

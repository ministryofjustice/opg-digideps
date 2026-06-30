<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class TableBuilder
{
    private ?Header $header;

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
        private readonly ?string $caption = null,
        private readonly bool $isHeaderHidden = false,
    ) {
        $this->header = null;
        $this->rows = [];
        $this->columns = null;
    }

    /**
     * @return array<Cell>
     */
    private function mapCells(string|RenderableInterface|Cell ...$cells): array
    {
        return array_map(fn (string|RenderableInterface|Cell $cell): Cell => $cell instanceof Cell ? $cell : new Cell($cell), $cells);
    }

    public function addHeader(string|RenderableInterface|Cell ...$cells): TableBuilder
    {
        $this->header = new Header(new Row(false, true, ...$this->mapCells(...$cells)), $this->isHeaderHidden);
        return $this;
    }

    public function addRow(string|RenderableInterface|Cell ...$cells): TableBuilder
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

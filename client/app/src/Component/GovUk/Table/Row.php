<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\Table;

final readonly class Row
{
    public array $cells;

    public function __construct(
        bool $sized,
        bool $firstColumnIsHeader,
        bool $isTableHeader,
        Cell ...$cells
    ) {
        $sizedCells = [];
        $isFirstColumn = true;
        $count = $sized ? array_sum(array_map(fn(Cell $cell): int => $cell->size, $cells)) : null;
        foreach ($cells as $cell) {
            $isHeader = $cell->isHeader ?? ($isFirstColumn && $firstColumnIsHeader);
            $hasScope = $isTableHeader || ($isFirstColumn && $isHeader);
            if ($cell->content !== '') {
                $isFirstColumn = false;
            }
            $sizedCells[] = new SizedCell(
                $cell->content,
                $isHeader,
                $hasScope,
                $cell->format,
                $count,
                $cell->size
            );
        }
        $this->cells = $sizedCells;
    }
}

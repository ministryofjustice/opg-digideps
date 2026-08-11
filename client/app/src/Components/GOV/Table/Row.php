<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

final readonly class Row
{
    public array $cells;

    public function __construct(
        bool $firstColumnIsHeader,
        bool $isTableHeader,
        Cell ...$cells
    ) {
        $patchedCells = [];
        $isFirstColumn = true;
        foreach ($cells as $cell) {
            $isHeader = ($isFirstColumn && $firstColumnIsHeader) || $isTableHeader;
            if ($cell->content !== '') {
                $isFirstColumn = false;
            }

            $patchedCells[] = $isHeader ? $cell->asHeader() : $cell;
        }
        $this->cells = $patchedCells;
    }
}

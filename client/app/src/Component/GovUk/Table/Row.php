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
        foreach ($cells as $index => $cell) {
            $isFirstColumn = $index === 0;
            $isHeader = $cell->isHeader ?? ($isFirstColumn && $firstColumnIsHeader);
            $hasScope = $isTableHeader || ($isFirstColumn && $isHeader);
            $sizedCells[] = new SizedCell(
                $cell->content,
                $isHeader,
                $hasScope,
                $cell->format,
                $sized ? count($cells) : null
            );
        }
        $this->cells = $sizedCells;
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\Table;

final readonly class Row
{
    public array $cells;

    public function __construct(bool $sized, bool $firstColumnIsHeader, Cell ...$cells)
    {
        $sizedCells = [];
        foreach ($cells as $index => $cell) {
            $sizedCells[] = new SizedCell(
                $cell->content,
                $cell->isHeader ?? ($index === 0 && $firstColumnIsHeader),
                $cell->format,
                $sized ? count($cells) : null
            );
        }
        $this->cells = $sizedCells;
    }
}

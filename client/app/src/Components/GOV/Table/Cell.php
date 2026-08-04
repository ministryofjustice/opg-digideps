<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class Cell
{
    public string $className;

    public function __construct(
        public RenderableInterface|string $content,
        public ?string $format = null,
        public bool $isHeader = false,
        public int $colspan = 1,
        public int $rowspan = 1,
        ?bool $isBold = null,
    ) {
        if ($this->colspan < 1 || $this->rowspan < 1) {
            throw new \DomainException();
        }
        $this->className = $this->calculateClassNames($this->format, $this->isHeader, $isBold ?? $this->isHeader);
    }

    public function asHeader(): Cell
    {
        return new Cell($this->content, $this->format, true, $this->colspan, $this->rowspan);
    }

    private function calculateClassNames(?string $format, bool $isHeader, bool $isBold): string
    {
        $classNames = ['govuk-table__cell'];
        if ($format === 'numeric') {
            $classNames[] = 'govuk-table__cell--numeric';
        }
        if ($isBold) {
            $classNames[] = $isHeader ? 'govuk-table__header' : 'govuk-!-font-weight-bold';
        }
        return implode(' ', $classNames);
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\Table;

final readonly class SizedCell
{
    public string $classNames;

    public function __construct(
        public string $content,
        public bool $isHeader = false,
        public bool $hasScope = false,
        ?string $format = null,
        ?int $count = null,
        int $size = 1,
    ) {
        $this->classNames = $this->calculateClassNames($format, $count, $size);
    }

    private function calculateClassNames(?string $format, ?int $count, int $size): string
    {
        $classNames = ['govuk-table__cell'];
        if ($this->isHeader) {
            $classNames[] = 'govuk-table__header';
        }
        if ($format === 'numeric') {
            $classNames[] = 'govuk-table__cell--numeric';
        }
        if ($count !== null && $size <= $count) {
            $classNames[] = $this->calculateSizeClassName($size, $count);
        }
        return implode(' ', $classNames);
    }

    private function calculateSizeClassName(int $size, int $count): string
    {
        $dividend = match ($size) {
            2 => 'two',
            3 => 'three',
            4 => 'fourth',
            default => 'one',
        };
        $divisor = match ($count) {
            2 => 'half',
            3 => 'third',
            4 => 'fourth',
            default => 'one',
        };

        return $dividend === $divisor ? 'govuk-!-width-full' : "govuk-!-width-{$dividend}-{$divisor}";
    }
}

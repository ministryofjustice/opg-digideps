<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GovUk\Table;

final readonly class SizedCell
{
    public string $classNames;

    public function __construct(
        public string $content,
        public bool $header = false,
        ?string $format = null,
        ?int $count = null,
    ) {
        $classNames = ['govuk-table__cell'];
        if ($this->header) {
            $classNames[] = 'govuk-table__header';
        }
        if ($format === 'numeric') {
            $classNames[] = 'govuk-table__cell--numeric';
        }
        if ($count !== null) {
            $classNames[] = match ($count) {
                1 => 'govuk-!-width-full',
                2 => 'govuk-!-width-one-half',
                3 => 'govuk-!-width-one-third',
                4 => 'govuk-!-width-one-fourth',
                default => null,
            };
        }

        $this->classNames = implode(' ', $classNames);
    }
}

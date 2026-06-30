<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GOV\Table;

final readonly class Column
{
    public function __construct(
        public ?string $width
    ) {
    }
}

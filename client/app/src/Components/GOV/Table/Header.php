<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Table;

final readonly class Header
{
    public function __construct(
        public Row $row,
        public bool $isHidden,
    ) {
    }
}

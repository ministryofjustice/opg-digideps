<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\Table;

final readonly class Header
{
    public function __construct(
        public Row $row,
        public bool $isHidden,
    ) {
    }
}

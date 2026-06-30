<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GOV\Summary;

final readonly class Widths
{
    public function __construct(
        public ?string $key = null,
        public ?string $value = null,
        public ?string $action = null,
    ) {
    }
}

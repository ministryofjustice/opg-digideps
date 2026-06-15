<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\Table;

final readonly class Cell
{
    public function __construct(
        public string $content,
        public ?string $format = null,
        public ?bool $isHeader = null,
    ) {
    }
}

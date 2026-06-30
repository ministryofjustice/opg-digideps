<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final readonly class Item
{
    public function __construct(
        public string $key,
        public string $value,
        public null $action = null
    ) {
    }
}

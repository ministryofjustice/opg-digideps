<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Breadcrumbs;

final readonly class Item
{
    public function __construct(
        public string $href,
        public string $text,
    ) {
    }
}

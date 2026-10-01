<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\DOC;

final readonly class Prop
{
    public function __construct(
        public string $name,
        public string $type,
        public string $default,
    ) {
    }
}

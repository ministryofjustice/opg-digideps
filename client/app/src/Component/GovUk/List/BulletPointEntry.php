<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final readonly class BulletPointEntry
{
    public string $widthClass;

    public function __construct(
        public string $key,
        public array $value,
    ) {
        $this->widthClass = 'govuk-!-width-one-half';
    }
}

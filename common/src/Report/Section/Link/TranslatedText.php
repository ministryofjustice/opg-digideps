<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section\Link;

final readonly class TranslatedText
{
    public function __construct(
        public string $domain,
        public string $key
    ) {
    }
}

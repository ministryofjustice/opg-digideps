<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section\Link;

final readonly class SectionLink
{
    public function __construct(
        public RoutedUrl $url,
        public TranslatedText $text,
    ) {
    }
}

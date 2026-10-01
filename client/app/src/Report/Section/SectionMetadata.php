<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Report\Section;

final readonly class SectionMetadata
{
    public function __construct(
        public SectionTexts $texts,
        public string $overviewUrl,
        public string $currentSectionUrl,
        public string $previousSectionUrl,
        public string $nextSectionUrl,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Accordion;

final readonly class Accordion
{
    /**
     * @var array<Section>
     */
    public array $sections;

    public function __construct(Section ...$sections)
    {
        $this->sections = $sections;
    }
}

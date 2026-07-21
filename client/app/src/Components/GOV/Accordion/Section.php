<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Accordion;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class Section
{
    /**
     * @var array<RenderableInterface>
     */
    public array $content;

    public function __construct(
        public string $heading,
        public ?string $summary,
        public bool $expanded,
        RenderableInterface ...$content,
    ) {
        $this->content = $content;
    }
}

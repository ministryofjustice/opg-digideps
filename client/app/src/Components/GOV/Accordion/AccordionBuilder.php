<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Accordion;

use OPG\Digideps\Frontend\Components\GOV\BodyText;
use OPG\Digideps\Frontend\Components\RenderableInterface;

final class AccordionBuilder
{
    /**
     * @var array<Section>
     */
    private array $sections;

    public function __construct(private readonly bool $expanded = false)
    {
        $this->sections = [];
    }

    /**
     * @param RenderableInterface|string|array<RenderableInterface|string> $content
     */
    public function addSection(string $heading, RenderableInterface|string|array $content, ?bool $expanded = null, ?string $summary = null): AccordionBuilder
    {
        $this->sections[] = new Section($heading, $summary, $expanded ?? $this->expanded, ...$this->processContent(...(!is_array($content) ? [$content] : $content)));

        return $this;
    }

    public function makeAccordion(): Accordion
    {
        return new Accordion(...$this->sections);
    }

    /**
     * @return array<RenderableInterface>
     */
    private function processContent(RenderableInterface|string ...$content): array
    {
        return array_map(fn (RenderableInterface|string $element): RenderableInterface => is_string($element) ? new BodyText($element) : $element, $content);
    }
}

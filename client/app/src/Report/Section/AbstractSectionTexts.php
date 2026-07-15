<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Report\Section;

use OPG\Digideps\Common\Report\ReportMetadata;
use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Common\Utility\Ghost;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-sealed SectionTexts
 * This class only exists to separate concerns.
 */
abstract class AbstractSectionTexts
{
    protected readonly ?SectionTexts $previous;
    protected readonly ?SectionTexts $next;

    final public function __construct(
        private readonly ReportSection $section,
        private readonly ReportMetadata $metadata,
        private readonly TranslatorInterface $translator,
        private readonly array $parameters
    ) {
        $previousSection = $this->metadata->getSectionBefore($this->section);
        $this->previous = $previousSection !== null ? Ghost::new(SectionTexts::class, function (SectionTexts $texts) use ($previousSection): void {
            $texts->__construct(
                $previousSection,
                $this->metadata,
                $this->translator,
                $this->parameters
            );
        }) : null;
        $nextSection = $this->metadata->getSectionAfter($this->section);
        $this->next = $nextSection !== null ? Ghost::new(SectionTexts::class, function (SectionTexts $texts) use ($nextSection): void {
            $texts->__construct(
                $nextSection,
                $this->metadata,
                $this->translator,
                $this->parameters
            );
        }) : null;
    }

    final public function hasTranslation(string $key, bool $common = false): bool
    {
        $key = $this->getKey($key, $common);
        $text = $this->translator->trans($this->getKey($key, $common));
        return $text !== $key && !empty($text);
    }

    private function getKey(string $key, bool $common = false): string
    {
        return $common ? "common.{$key}" : "{$key}.{$this->section->value}";
    }

    final protected function translate(string $key, bool $common = false): string
    {
        $key = $this->getKey($key, $common);
        return $this->translator->trans($key, $this->parameters, 'report-sections');
    }
}

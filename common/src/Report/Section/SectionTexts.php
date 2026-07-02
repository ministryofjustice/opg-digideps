<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Report\Section;

use Symfony\Contracts\Translation\TranslatorInterface;

final class SectionTexts
{
    public function __construct(
        private readonly SectionMetadata $metadata,
        private readonly TranslatorInterface $translator
    ) {
    }

    public string $header { get => $this->translate('header'); }

    private function translate(string $key): string
    {
        return $this->translator->trans("{$key}.{$this->metadata->section->value}", domain: 'report-sections');
    }
}

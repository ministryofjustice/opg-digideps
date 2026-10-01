<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Report\Section;

final class SectionTexts extends AbstractSectionTexts
{
    public string $title { get => $this->translate('title'); }
    public string $link { get => $this->title; }
    public string $beforeStart { get => $this->translate('beforeStart', true); }
    public string $previousLink { get => $this->previous->link ?? $this->overviewLink; }
    public string $nextLink { get => $this->next->link ?? $this->overviewLink; }
    public string $overviewLink { get => $this->translate('overview.link', true); }
}

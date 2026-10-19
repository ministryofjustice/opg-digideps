<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\List;

use OPG\Digideps\Frontend\Components\RenderableInterface;

abstract class AbstractList implements RenderableInterface
{
    public string $componentName = 'GOV:List';
    public array $props { get => ['list' => $this]; }

    /**
     * @var array<string|RenderableInterface>
     */
    public readonly array $items;

    protected function __construct(
        private readonly ListTag $type,
        private readonly bool $decorated,
        string|RenderableInterface ...$items
    ) {
        $this->items = $items;
    }

    public string $tag { get => $this->type->value;}
    public string $className {
        get {
            $decorationClass = $this->decorated ? match ($this->type) {
                ListTag::Ordered => ' govuk-list--number',
                ListTag::Unordered => ' govuk-list--bullet',
            } : '';
            return "govuk-list{$decorationClass}";
        }
    }
}

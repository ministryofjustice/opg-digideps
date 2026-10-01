<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\GOV\Tag;
use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class TaggedValue implements RenderableInterface
{
    public string $componentName;
    public array $props;

    public function __construct(public string $value, public Tag $tag)
    {
        $this->componentName = 'OPG:Renderable:TaggedValue';
        $this->props = ['value' => $this->value, 'tag' => $this->tag];
    }
}

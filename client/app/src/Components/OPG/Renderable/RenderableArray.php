<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class RenderableArray implements RenderableInterface
{
    public string $componentName;
    public array $props;

    public function __construct(RenderableInterface|RenderableArray|string ...$elements)
    {
        $this->componentName = 'Render';
        $this->props = ['self' => $elements];
    }
}

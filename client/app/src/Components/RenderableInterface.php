<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components;

interface RenderableInterface
{
    public string $componentName {get;}
    public array $props {get;}
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\GOV\Link;
use OPG\Digideps\Frontend\Components\RenderableInterface;

class ActionsList implements RenderableInterface
{
    public function __construct(
        /** @var array<Link> $links */
        private readonly array $links
    ) {
    }

    public string $componentName {
        get {
            return 'OPG:Renderable:ActionsList';
        }
    }
    public array $props {
        get {
            return [
                'links' => $this->links
            ];
        }
    }
}

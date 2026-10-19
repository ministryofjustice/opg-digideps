<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Tag implements RenderableInterface
{
    public const string GREY = 'grey';
    public const string GREEN = 'green';
    public const string TEAL = 'teal';
    public const string BLUE = 'blue';
    public const string PURPLE = 'purple';
    public const string MAGENTA = 'magenta';
    public const string RED = 'red' ;
    public const string ORANGE = 'orange';
    public const string YELLOW = 'yellow';

    public readonly string $componentName;

    public function __construct(public readonly string $text, public readonly string $colour)
    {
        $this->componentName = 'GOV:Tag';
    }

    public array $props {
        get {
            return [
                'text' => $this->text,
                'colour' => $this->colour,
            ];
        }
    }
}

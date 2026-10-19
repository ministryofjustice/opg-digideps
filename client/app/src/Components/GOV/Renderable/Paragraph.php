<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\GOV\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final class Paragraph implements RenderableInterface
{
    /**
     * @var array<string|Link>
     */
    private readonly array $text;

    public function __construct(
        string|Link ...$text
    ) {
        $this->text = $text;
    }

    public string $componentName {get => 'GOV:Renderable:Paragraph';}
    public array $props {get => ['text' => $this->text];}
}

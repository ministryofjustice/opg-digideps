<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\DOC;

final readonly class Example
{
    public string $template;
    public string $code;

    public function __construct(?string $namespace, string $identifier, public array $props = [])
    {
        $namespace = $namespace === null ? '' : "/{$namespace}";
        $path = "twig-examples{$namespace}/{$identifier}.html.twig";
        $this->template = "@App/{$path}";
        $this->code = file_get_contents(__DIR__ . "/../../../templates/{$path}") ?: '';
    }
}

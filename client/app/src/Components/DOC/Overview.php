<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\DOC;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Overview
{
    /**
     * @var array<string> $components
     */
    public array $components = [];

    /**
     * @param array<string> $components
     */
    public function mount(?array $components = null): void
    {
        if (!empty($components)) {
            $this->components = $components;
        } else {
            $root = __DIR__ . '/../../../templates/twig-components/';
            foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root)), '/^.+\.html.json/i', \RegexIterator::GET_MATCH) as $result) {
                /**
                 * @var array<string> $result
                 */
                $path = substr($result[0], strlen($root), -10);
                $path = str_ends_with($path, 'index') ? substr($path, 0, -6) : $path;
                $this->components[] = str_replace('/', ':', $path);
            }
        }
        sort($this->components);
    }
}

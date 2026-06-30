<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\DesignSystem;

use OPG\Digideps\Frontend\Component\GOV\Table\Table;
use OPG\Digideps\Frontend\Component\GOV\Table\TableBuilder;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @phpstan-type PropsArray array{name: string, type: string, default: null|string}
 * @phpstan-type DocumentationArray array{namespace: null|string, path: null|string, link: null|string, info: null|string, warning: null|string, props: array<PropsArray>, examples: array<string>}
 */
#[AsTwigComponent]
final class Documentation
{
    public ?string $name = null;
    public ?string $path = null;
    public ?string $link = null;
    public ?string $warning = null;
    public ?string $info = null;
    public array $examples = [];
    public ?Table $props = null;

    public function mount(string $component): void
    {
        $this->name = $component;
        $json = $this->fetchDocumentation($component);

        if ($json === null) {
            return;
        }

        $this->path = $json['path'];
        $this->warning = $json['warning'];
        $this->info = $json['info'];
        $this->link = $json['link'];
        if (!empty($json['examples'])) {
            $this->examples = array_map(fn (string $example) => new Example($json['namespace'], $example), $json['examples']);
        }
        if (!empty($json['props'])) {
            $builder = new TableBuilder(caption: 'Props')->addHeader('Name', 'Type', 'Default');
            foreach ($json['props'] as $prop) {
                $builder->addRow($prop['name'], $prop['type'], $prop['default'] ?? '');
            }
            $this->props = $builder->makeTable();
        }
    }

    /**
     * @return DocumentationArray|null
     */
    private function fetchDocumentation(string $component): ?array
    {
        $parts = explode(':', $component);
        $namespace = count($parts) > 1 ? $parts[0] : null;
        $root = __DIR__ . '/../../../templates/twig-components/';
        $base = $root . implode('/', $parts);
        $path = "{$base}.html.json";
        if (!file_exists($path)) {
            $path = "{$base}/index.html.json";
        }
        if (file_exists($path)) {
            $file = file_get_contents($path);
            if ($file) {
                $json = json_decode($file, true);
                if (is_array($json)) {
                    $json['namespace'] = $namespace;
                    $json['path'] = substr($path, strlen($root), -4) . 'twig';
                    $json['warning'] ??= null;
                    $json['link'] ??= null;
                    $json['info'] ??= null;
                    $json['props'] ??= null;
                    $json['examples'] ??= null;
                    if (is_array($json['props'])) {
                        /**
                         * @var array<PropsArray> $props
                         */
                        $props = $json['props'];
                        $json['props'] = array_map(fn (array $props): array => [...$props, 'default' => $props['default'] ?? null], $props);
                    }
                    /**
                     * @var DocumentationArray $json
                     */
                    return $json;
                }
            }
        }
        return null;
    }
}

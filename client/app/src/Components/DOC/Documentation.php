<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\DOC;

use OPG\Digideps\Frontend\Components\GOV\List\ListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Summary\SummaryListBuilder;
use OPG\Digideps\Frontend\Components\GOV\Table\Table;
use OPG\Digideps\Frontend\Components\GOV\Table\TableBuilder;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @phpstan-type PropsArray array{name: string, type: string, default: null|string, info: null|string}
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
            $extraProps = $this->makeExtraProps();
            $this->examples = array_map(fn (string $example) => new Example($json['namespace'], $example, $extraProps), $json['examples']);
        }
        if (!empty($json['props'])) {
            $builder = new TableBuilder(caption: 'Props')->addHeader('Name', 'Type', 'Default', 'Info');
            foreach ($json['props'] as $prop) {
                $builder->addRow($prop['name'], $prop['type'], $prop['default'] ?? '', $prop['info'] ?? '');
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
                        $json['props'] = array_map(fn (array $props): array => [
                            ...$props,
                            'default' => $props['default'] ?? null,
                            'info' => $props['info'] ?? null
                        ], $props);
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

    private function makeExtraProps(): array
    {
        $summaryList = new SummaryListBuilder()
            ->addItem('Question 1', 'Alpha')
            ->addItem('Question 2', 'Beta')
            ->addItem('Question 3', 'Gamma')
            ->addItem('Question 4', 'Delta')
            ->makeList();
        $listBuilder = new ListBuilder()
            ->addItem("Alpha")
            ->addItem('Beta')
            ->addItem('Gamma')
            ->addItem('Delta');
        $orderedList = $listBuilder->makeOrderedList();
        $unorderedList = $listBuilder->makeUnorderedList();
        $table = new TableBuilder()->addColumns(1, 2, 1)
            ->addHeader('Fii', 'Foo', 'Fuu')
            ->addRow('Hello', 'Hello World', 'World')
            ->addRow('Quod', "Erat", "Demonstrandum")
            ->makeTable();
        $tableWithCaption = new TableBuilder(caption: 'The quick brown fox jumps over the lazy dog')->addColumns(1, 1, 2)
            ->addRow('Fii', 'Foo', 'Fuu')
            ->addHeader('Quod', "Erat", "Demonstrandum")
            ->addRow($unorderedList, $orderedList, 'World')

            ->makeTable();
        return [
            'list' => $summaryList,
            'orderedList' => $orderedList,
            'unorderedList' => $unorderedList,
            'table' => $table,
            'tableWithCaption' => $tableWithCaption
        ];
    }
}

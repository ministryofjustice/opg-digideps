<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\RichText;

use OPG\Digideps\Frontend\Components\GOV\List\ListBuilder;
use OPG\Digideps\Frontend\Components\GOV\List\UnorderedList;
use OPG\Digideps\Frontend\Components\GOV\Renderable\Link;
use OPG\Digideps\Frontend\Components\GOV\Renderable\Paragraph;
use OPG\Digideps\Frontend\Components\GOV\Renderable\Heading;
use OPG\Digideps\Frontend\Components\OPG\Renderable\RenderableArray;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RichTextParser
{
    /**
     * @var callable(string): array{string, string} $linkResolver
     */
    private mixed $linkResolver;

    /**
     * @param array<string, string> $parameters
     * @param null|callable(string): array{string, string} $linkResolver
     */
    public function __construct(
        private TranslatorInterface $translator,
        private array $parameters = [],
        ?callable $linkResolver = null,
    ) {
        $this->linkResolver = $linkResolver ?? [$this, 'defaultLinkResolver'];
    }

    public function parse(string $key, string $domain, int $h = 1): RenderableInterface
    {
        $raw = $this->translator->trans($key, [], $domain);
        $lines = array_filter(explode("\n", $raw));
        $renderBuffer = [];
        $listBuffer = [];
        foreach ($lines as $line) {
            $this->parseLine($line, $listBuffer, $renderBuffer, $h);
        }
        $this->parseLine(null, $listBuffer, $renderBuffer, $h);

        return new RenderableArray(...$renderBuffer);
    }

    /**
     * @param array<string> $listBuffer
     * @param array<RenderableInterface> $renderBuffer
     */
    private function parseLine(?string $line, array &$listBuffer, array &$renderBuffer, int $h): void
    {
        if ($line === null) {
            if (!empty($listBuffer)) {
                $renderBuffer[] = $this->handleList(...$listBuffer);
            }
        } elseif (!empty($listBuffer) && $line[0] !== '-') {
            $renderBuffer[] = $this->handleList(...$listBuffer);
            $listBuffer = [];
            $this->parseLine($line, $listBuffer, $renderBuffer, $h);
        } elseif ($line[0] === '-') {
            $listBuffer[] = mb_trim(mb_substr($line, 1));
        } elseif ($line[0] === '#') {
            $renderBuffer[] = $this->handleHeader($line, $h);
        } else {
            $renderBuffer[] = $this->handleParagraph($line);
        }
    }

    private function handleList(string ...$lines): UnorderedList
    {
        $builder = new ListBuilder();
        foreach ($lines as $line) {
            $builder->addItem($this->handleText($line));
        }
        return $builder->makeUnorderedList();
    }

    private function handleHeader(string $line, int $h): Heading
    {
        $line = mb_trim(mb_substr($line, 1));
        return new Heading($this->handleText($line), $h);
    }

    private function handleParagraph(string $line): Paragraph
    {
        $parts = [];
        $matches = [];
        foreach (preg_split('~(\[[^]]*]\([^)]+\))~', $line, flags: PREG_SPLIT_DELIM_CAPTURE) ?: [] as $part) {
            $parts[] = match (preg_match('~\[([^]]*)]\(([^)]+)\)~', $part, $matches, flags: PREG_UNMATCHED_AS_NULL)) {
                1 => $this->handleLink($matches[1] ?? '', $matches[2] ?? ''),
                default => $this->handleText($part)
            };
        }
        return new Paragraph(...$parts);
    }


    private function handleLink(string $hint, string $url): Link
    {
        [$text, $href] = ($this->linkResolver)($url);
        $text = match ($hint) {
            '_' => $this->lowercase($text),
            '^' => $this->uppercase($text),
            '' => $text,
            default => $hint
        };
        return new Link($this->handleText($text), $href, true);
    }

    private function uppercase(string $text): string
    {
        if (!empty($text)) {
            $text[0] = mb_strtoupper($text[0]);
        }
        return $text;
    }

    private function lowercase(string $text): string
    {
        if (!empty($text)) {
            $text[0] = mb_strtolower($text[0]);
        }
        return $text;
    }

    private function handleText(string $raw): string
    {
        return str_replace(array_keys($this->parameters), array_values($this->parameters), $raw);
    }

    /**
     * @return array{string, string}
     */
    private function defaultLinkResolver(string $uri): array
    {
        return [$uri, $uri];
    }
}

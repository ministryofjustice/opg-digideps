<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final class ListBuilder
{
    /**
     * @var array<Entry> $entries
     */
    private array $entries;

    public function __construct()
    {
        $this->entries = [];
    }

    public function addEntry(string $key, string $value): ListBuilder
    {
        $this->entries[] = new Entry($key, $value);
        return $this;
    }

    public function makeList(): DefinitionList
    {
        return new DefinitionList(...$this->entries);
    }
}

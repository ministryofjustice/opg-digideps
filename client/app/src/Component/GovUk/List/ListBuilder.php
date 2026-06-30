<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Component\GovUk\List;

final class ListBuilder
{
    /**
     * @var array<Item> $items
     */
    private array $items;
    private bool $hasActions;

    public function __construct(
        private readonly bool $sized = true,
        private readonly int $keySize = 1,
        private readonly int $valueSize = 1,
        private readonly int $actionSize = 1,
    ) {
        $this->items = [];
        $this->hasActions = false;
        if ($this->sized && ($this->keySize < 1 || $this->valueSize < 1 || $this->actionSize < 1)) {
            throw new \DomainException("");
        }
    }

    public function addItem(string $key, string $value, null $action = null): ListBuilder
    {
        $this->items[] = new Item($key, $value, $action);
        if ($action !== null) {
            $this->hasActions = true;
        }
        return $this;
    }

    public function makeList(): DefinitionList
    {
        $count = $this->keySize + $this->valueSize + ($this->hasActions ? $this->actionSize : 0);
        $sized = $this->sized && $count <= 4;

        return new DefinitionList($sized ? new Widths(
            $this->getWidthAsFraction($this->keySize, $count),
            $this->getWidthAsFraction($this->valueSize, $count),
            $this->hasActions ? $this->getWidthAsFraction($this->actionSize, $count) : null,
        ) : new Widths(), ...$this->items);
    }

    private function getWidthAsFraction(int $size, int $count): ?string
    {
        $dividend = match ($size) {
            1 => 'one',
            2 => 'two',
            3 => 'three',
            default => null,
        };
        $plural = $size === 1 ? '' : 's';
        $divisor = match ($count) {
            2 => 'half',
            3 => "third{$plural}",
            4 => "quarter{$plural}",
            default => null,
        };

        return $dividend === null || $divisor === null ? null : "{$dividend}-{$divisor}";
    }
}

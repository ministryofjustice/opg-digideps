<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Counter;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'counter')]
#[Entity]
class Counter
{
    public const int FIXTURE_ID = 1;

    public function __construct(
        #[Id, Column(name: 'id', type: 'integer', nullable: false)]
        public readonly int $id = 0,
        #[Column(name: 'counter', type: 'integer', nullable: false)]
        private int $counter = 0,
    ) {
    }

    public function reset(int $counter = 0): void
    {
        $this->counter = $counter;
    }

    public function nextInt(): int
    {
        $this->counter++;
        return $this->counter;
    }

    public function nextString(int $size = 0, string $prefix = '', string $postfix = ''): string
    {
        $size -= strlen($prefix) + strlen($postfix);
        $pad = $size > 0 ? "$0{$size}s" : 's';
        return sprintf("{$prefix}%1{$pad}{$postfix}", $this->nextInt());
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class BankAccount implements RenderableInterface
{
    public string $componentName;
    public array $props;

    public function __construct(
        public string $type,
        public string $number,
        public ?string $sortCode,
        public ?string $bankName,
        public bool $joint,
        public bool $closed
    ) {
        $this->componentName = 'OPG:Renderable:BankAccount';
        $this->props = ['account' => $this];
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class Address implements RenderableInterface
{
    public string $componentName;
    public array $props;

    public function __construct(
        public ?string $address1,
        public ?string $address2,
        public ?string $address3,
        public ?string $address4,
        public ?string $address5,
        public ?string $postcode,
        public ?string $country,
    ) {
        $this->componentName = 'OPG:Renderable:Address';
        $this->props = ['address' => $this];
    }
}

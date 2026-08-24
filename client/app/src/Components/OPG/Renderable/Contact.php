<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Renderable;

use OPG\Digideps\Frontend\Components\RenderableInterface;

final readonly class Contact implements RenderableInterface
{
    public string $componentName;
    public array $props;

    public function __construct(
        public string $name,
        public ?string $address1,
        public ?string $address2,
        public ?string $county,
        public ?string $postcode,
        public ?string $country
    ) {
        $this->componentName = 'OPG:Renderable:Contact';
        $this->props = ['contact' => $this];
    }
}

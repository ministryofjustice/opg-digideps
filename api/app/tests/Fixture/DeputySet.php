<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Backend\Fixture;

use OPG\Digideps\Common\Deputy\DeputyType;

final readonly class DeputySet
{
    /**
     * @var array<DeputyDescriptor>
     */
    public array $descriptors;

    public function __construct(DeputyDescriptor ...$descriptors)
    {
        $this->descriptors = $descriptors;
    }

    public static function oneLay(string $deputyReference = 'lay1'): DeputySet
    {
        return new DeputySet(new DeputyDescriptor($deputyReference, DeputyType::LAY));
    }

    public static function oneNamedPro(string $deputyReference = 'pro1'): DeputySet
    {
        return new DeputySet(new DeputyDescriptor($deputyReference, DeputyType::PRO));
    }

    public static function oneNamedPa(string $deputyReference = 'pa1'): DeputySet
    {
        return new DeputySet(new DeputyDescriptor($deputyReference, DeputyType::PA));
    }
}

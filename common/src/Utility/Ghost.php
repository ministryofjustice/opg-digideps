<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Utility;

final readonly class Ghost
{
    private function __construct()
    {
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param callable(T): void $initialiser
     * @return T
     */
    public static function new(string $class, callable $initialiser): object
    {
        return new \ReflectionClass($class)->newLazyGhost($initialiser);
    }
}

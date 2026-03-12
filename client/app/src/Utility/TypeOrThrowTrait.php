<?php

declare(strict_types=1);

namespace App\Utility;

trait TypeOrThrowTrait
{
    abstract protected function getUnvalidated(string|int|null $key): mixed;

    public function getIntegerOrThrow(string|int|null $key): int
    {
        $value = $this->getUnvalidated($key);
        return is_int($value) ? $value : throw new ValidationException('int', $value);
    }

    public function getStringOrThrow(string|int|null $key): string
    {
        $value = $this->getUnvalidated($key);
        return is_string($value) ? $value : throw new ValidationException('string', $value);
    }

    public function getFloatOrThrow(string|int|null $key): float
    {
        $value = $this->getUnvalidated($key);
        return is_float($value) ? $value : throw new ValidationException('float', $value);
    }

    public function getArrayOrThrow(string|int|null $key): array
    {
        $value = $this->getUnvalidated($key);
        return is_array($value) ? $value : throw new ValidationException('array', $value);
    }

    /**
     * @template C of object
     * @param class-string<C> $class
     * @return C
     */
    public function getObjectOrThrow(string|int|null $key, string $class): object
    {
        $value = $this->getUnvalidated($key);
        return is_object($value) && is_a($value, $class, true) ? $value : throw new ValidationException($class, $value);
    }
}

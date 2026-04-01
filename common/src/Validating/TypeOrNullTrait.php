<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

trait TypeOrNullTrait
{
    abstract protected function getUnvalidated(string|int|null $key): mixed;

    public function getIntegerOrNull(string|int|null $key): ?int
    {
        $value = $this->getUnvalidated($key);
        return is_int($value) ? $value : null;
    }

    public function getStringOrNull(string|int|null $key): ?string
    {
        $value = $this->getUnvalidated($key);
        return is_string($value) ? $value : null;
    }

    public function getFloatOrNull(string|int|null $key): ?float
    {
        $value = $this->getUnvalidated($key);
        return is_float($value) ? $value : null;
    }

    public function getArrayOrNull(string|int|null $key): ?array
    {
        $value = $this->getUnvalidated($key);
        return is_array($value) ? $value : null;
    }

    /**
     * @template C of object
     * @param class-string<C> $class
     * @return C|null
     */
    public function getObjectOrNull(string|int|null $key, string $class): ?object
    {
        $value = $this->getUnvalidated($key);
        return is_object($value) && is_a($value, $class, true) ? $value : null;
    }
}

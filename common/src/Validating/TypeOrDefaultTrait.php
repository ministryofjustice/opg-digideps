<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

trait TypeOrDefaultTrait
{
    abstract protected function getUnvalidated(string|int|null $key): mixed;

    public function getIntegerOrDefault(string|int|null $key, int $default): int
    {
        $value = $this->getUnvalidated($key);
        return is_int($value) ? $value : $default;
    }

    public function getStringOrDefault(string|int|null $key, string $default): string
    {
        $value = $this->getUnvalidated($key);
        return is_string($value) ? $value : $default;
    }

    public function getFloatOrDefault(string|int|null $key, float $default): float
    {
        $value = $this->getUnvalidated($key);
        return is_float($value) ? $value : $default;
    }

    /**
     * @param array<mixed> $default
     * @return array<mixed>
     */
    public function getArrayOrDefault(string|int|null $key, array $default): array
    {
        $value = $this->getUnvalidated($key);
        return is_array($value) ? $value : $default;
    }
}

<?php

declare(strict_types=1);

namespace App\Utility;

final readonly class ValidatingArray
{
    use TypeOrZeroTrait;
    use TypeOrDefaultTrait;
    use TypeOrThrowTrait;

    public function __construct(private array $data)
    {
    }

    protected function getUnvalidated(int|string|null $key): mixed
    {
        return $key === null ? null : $this->data[$key] ?? null;
    }

    public function getValidatingObjectOrNull(string|int|null $key): ?ValidatingArray
    {
        $value = $this->getArrayOrNull($key);
        return $value !== null ? new ValidatingArray($value) : null;
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

final readonly class ValidatingArray
{
    use TypeOrNullTrait;
    use TypeOrDefaultTrait;
    use TypeOrThrowTrait;

    /**
     * @param array<mixed> $data
     */
    public function __construct(private array $data)
    {
    }

    protected function getUnvalidated(int|string|null $key): mixed
    {
        return $key === null ? null : $this->data[$key] ?? null;
    }

    public function getValidatingArrayOrNull(string|int|null $key): ?ValidatingArray
    {
        $value = $this->getArrayOrNull($key);
        return $value !== null ? new ValidatingArray($value) : null;
    }
}

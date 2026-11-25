<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

final readonly class ValidatingArray
{
    use TypeOrNullTrait;
    use TypeOrDefaultTrait;
    use TypeOrThrowTrait;

    public function __construct(private array $data)
    {
    }

    protected function getUnvalidated(int|string|null $key): mixed
    {
        return $key === null ? array_first($this->data) : $this->data[$key] ?? null;
    }

    /**
     * @return array<int|string>
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    public function getValidatingArrayOrNull(string|int|null $key): ?ValidatingArray
    {
        $value = $this->getArrayOrNull($key);
        return $value !== null ? new ValidatingArray($value) : null;
    }
}

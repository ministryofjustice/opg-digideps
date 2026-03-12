<?php

declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Form\FormInterface;

final readonly class ValidatingForm
{
    use TypeOrZeroTrait;
    use TypeOrDefaultTrait;
    use TypeOrThrowTrait;

    public function __construct(private FormInterface $data)
    {
    }

    protected function getUnvalidated(int|string|null $key): mixed
    {
        return $key === null ? $this->data->getData() : $this->data[$key]?->getData() ?? null;
    }

    public function getValidatingFormOrNull(string $key): ?ValidatingForm
    {
        return $this->data->has($key) ? new ValidatingForm($this->data->get($key)) : null;
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Common\Validating;

use Symfony\Component\Form\Button;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

final readonly class ValidatingForm
{
    use TypeOrNullTrait;
    use TypeOrDefaultTrait;
    use TypeOrThrowTrait;

    public function __construct(private FormInterface $data)
    {
    }

    protected function getUnvalidated(int|string|null $key): mixed
    {
        $form = $key === null ? $this->data->getData() : $this->getFormOrNull((string)$key);
        return $form instanceof Form ? $form->getData() : $form;
    }

    public function getValidatingFormOrNull(string $key): ?ValidatingForm
    {
        $form = $this->getFormOrNull($key);
        return $form !== null ? new ValidatingForm($form) : null;
    }

    private function getFormOrNull(string $key): ?FormInterface
    {
        return $this->data->has($key) ? $this->data->get($key) : null;
    }
}

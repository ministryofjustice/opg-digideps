<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Form\Component;

use OPG\Digideps\Common\Validating\TypeOrDefaultTrait;
use OPG\Digideps\Common\Validating\TypeOrNullTrait;
use OPG\Digideps\Common\Validating\TypeOrThrowTrait;
use OPG\Digideps\Common\Validating\ValidatingForm;
use OPG\Digideps\Frontend\Components\GOV\Form;
use Symfony\Component\Form\Form as SymfonyForm;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template T of AbstractFormType
 */
final class RenderableForm
{
    use TypeOrNullTrait;
    use TypeOrDefaultTrait;
    use TypeOrThrowTrait;

    /**
     * @param T $type
     */
    public function __construct(private readonly AbstractFormType $type, private readonly FormInterface $form)
    {
    }

    protected function getUnvalidated(int|string|null $key): mixed
    {
        $form = $key === null ? $this->form->getData() : $this->getFormOrNull((string)$key);
        return $form instanceof SymfonyForm ? $form->getData() : $form;
    }

    public function getValidatingFormOrNull(string $key): ?ValidatingForm
    {
        $form = $this->getFormOrNull($key);
        return $form !== null ? new ValidatingForm($form) : null;
    }

    private function getFormOrNull(string $key): ?FormInterface
    {
        return $this->form->has($key) ? $this->form->get($key) : null;
    }

    /**
     * Returns true if the form is submitted, is valid, and the request method is as expected.
     */
    public function handleRequest(Request $request, string $onMethod = 'POST'): bool
    {
        if ($request->getMethod() !== $onMethod) {
            return false;
        }
        $this->form->handleRequest($request);
        return $this->form->isSubmitted() && $this->form->isValid();
    }

    public function addError(FormError $error): void
    {
        $this->form->addError($error);
    }

    public function render(): Form
    {
        return $this->type->render($this->form->createView());
    }
}

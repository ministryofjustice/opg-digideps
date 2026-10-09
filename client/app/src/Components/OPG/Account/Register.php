<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Account;

use OPG\Digideps\Common\Account\AccountError;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Register
{
    /** @var array<string, ?string> */
    public array $text;

    public FormView $form;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function mount(FormView $form, ?AccountError $accountCreationError, bool $accountCreated): void
    {
        $this->form = $form;

        $this->text = [
            'accountCreationErrorMessage' => $accountCreationError?->value,
            'accountCreatedMessage' => $accountCreated ? 'ACCOUNT SUCCESSFULLY CREATED' : null,
            'intro' => $this->translate('opg.register.intro'),
            'signIn' => $this->translate('opg.register.signIn'),
            'ifYouAlreadyHaveAnAccount' => $this->translate('opg.register.ifYouAlreadyHaveAnAccount')
        ];
    }

    private function translate(string $id): string
    {
        try {
            return $this->translator->trans($id, [], 'twig-components');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}

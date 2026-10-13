<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Account;

use OPG\Digideps\Common\Account\AccountError;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Request
{
    /** @var array<string, ?string> */
    public array $text;

    /** @var array<string, string|bool|null|AccountError> */
    public array $props;

    public FormView $form;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function mount(FormView $form, ?AccountError $accountCreationError, bool $accountCreated, ?string $deputyEmail): void
    {
        $this->form = $form;

        $this->props = [
            'accountCreated' => $accountCreated,
            'accountCreationFailed' => $accountCreationError !== null,
            'deputyEmail' => $deputyEmail,
            'loginPath' => 'login',
        ];

        $signInText = $this->translate('opg.register.signIn');
        $signInTextCaps = ucfirst($signInText);
        $this->text = [
            'accountCreationErrorMessage' => null,
            'intro' => $this->translate('opg.register.intro'),
            'signIn' => $signInText,
            'signInCaps' => $signInTextCaps,
            'ifYouAlreadyHaveAnAccount' => $this->translate('opg.register.ifYouAlreadyHaveAnAccount'),
            'accountCreatedSubHeading' => $this->translate('opg.register.accountCreated.subHeading'),
            'linkSentTo' => $this->translate('opg.register.accountCreated.linkSentTo'),
            'activationReminder' => $this->translate('opg.register.accountCreated.activationReminder'),
            'expirationNote' => $this->translate('opg.register.accountCreated.expirationNote'),
            'checkJunk' => $this->translate('opg.register.accountCreated.checkJunk')
        ];

        if ($accountCreationError !== null) {
            $this->text['accountCreationErrorMessage'] = $this->translate("opg.register.errors.{$accountCreationError->value}");
        }
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

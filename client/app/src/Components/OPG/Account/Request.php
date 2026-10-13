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

    /** @var array<string, string|bool|null> */
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
            'loginPath' => 'login'
        ];

        $signInText = $this->translate('opg.account.request.signIn');
        $signInTextCaps = ucfirst($signInText);
        $this->text = [
            'accountCreationErrorMessage' => null,
            'deputyEmail' => $deputyEmail,
            'intro' => $this->translate('opg.account.request.intro'),
            'signIn' => $signInText,
            'signInCaps' => $signInTextCaps,
            'ifYouAlreadyHaveAnAccount' => $this->translate('opg.account.request.ifYouAlreadyHaveAnAccount'),
            'accountCreatedSubHeading' => $this->translate('opg.account.request.accountCreated.subHeading'),
            'linkSentTo' => $this->translate('opg.account.request.accountCreated.linkSentTo'),
            'activationReminder' => $this->translate('opg.account.request.accountCreated.activationReminder'),
            'expirationNote' => $this->translate('opg.account.request.accountCreated.expirationNote'),
            'checkJunk' => $this->translate('opg.account.request.accountCreated.checkJunk')
        ];

        if ($accountCreationError !== null) {
            $this->text['accountCreationErrorMessage'] = $this->translate(
                "opg.account.request.errors.{$accountCreationError->value}",
                ['%helplineNumber%' => $this->translate('opg.helplineNumber')]
            );
        }
    }

    private function translate(string $id, array $params = []): string
    {
        try {
            return $this->translator->trans($id, $params, 'twig-components');
        } catch (\Throwable $t) {
            return "$t";
        }
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Account;

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

    public function mount(FormView $form, bool $accountCreated, ?string $deputyEmail): void
    {
        $this->form = $form;

        $this->props = [
            'accountCreated' => $accountCreated,
            'loginPath' => 'login'
        ];

        $signInText = $this->translate('opg.account.request.signIn');
        $signInTextCaps = ucfirst($signInText);
        $this->text = [
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

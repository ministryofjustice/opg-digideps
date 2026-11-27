<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Account;

use OPG\Digideps\Frontend\Components\GOV\Form;
use OPG\Digideps\Frontend\Components\OPG\RichText\RichTextParser;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Request
{
    /** @var array<string, ?string> */
    public array $text = [];

    public RenderableInterface $content;

    /** @var array<string, string|bool|null> */
    public array $props;

    public Form $form;

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function mount(Form $form, bool $accountCreated, ?string $deputyEmail): void
    {
        $this->form = $form;
        $this->props = ['accountCreated' => $accountCreated];

        $signInText = $this->translator->trans('opg.account.request.signIn', [], 'twig-components');
        $contentKey = $accountCreated ? 'opg.account.created.content' : 'opg.account.request.content';

        $this->content = new RichTextParser($this->translator, [
            '%signInPath%' => '/login',
            '%signIn%' => $signInText,
            '%signInCaps%' => ucfirst($signInText),
            '%deputyEmail%' => $deputyEmail ?? ''
        ])->parse($contentKey, 'twig-components');
    }
}

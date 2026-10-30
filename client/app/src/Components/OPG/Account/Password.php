<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Account;

use OPG\Digideps\Frontend\Components\GOV\Form;
use OPG\Digideps\Frontend\Components\OPG\RichText\RichTextParser;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Password
{
    public Form $form;
    public RenderableInterface $content;

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function mount(bool $isReset): void
    {
        $action = $isReset ? 'reset' : 'set';
        $this->content = new RichTextParser($this->translator, [
            '%action%' => $this->translator->trans("opg.account.password.{$action}.action", [], 'twig-components'),
            '%quality%' => $this->translator->trans("opg.account.password.{$action}.quality", [], 'twig-components'),
        ])
            ->parse('opg.account.password.content', 'twig-components');
    }
}

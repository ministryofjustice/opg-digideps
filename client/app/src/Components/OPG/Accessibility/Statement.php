<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Accessibility;

use OPG\Digideps\Frontend\Components\OPG\RichText\RichTextParser;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Statement
{
    public RenderableInterface $content;

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
        $params = [
            '%emailAddress%' => $this->translator->trans('contact.digideps.emailAddress', [], 'twig-components'),
            '%telephoneNumber%' => $this->translator->trans('contact.digideps.telephoneNumber', [], 'twig-components'),
            '%textphoneNumber%' => $this->translator->trans('contact.digideps.textphoneNumber', [], 'twig-components'),
            '%openingHours%' => $this->translator->trans('contact.digideps.openingHours', [], 'twig-components'),
            '%customerServiceEmail%' => $this->translator->trans('contact.opg.customerServiceEmail', [], 'twig-components'),
        ];

        $this->content = new RichTextParser($this->translator, $params)
            ->parse('opg.accessibility.statement.content', 'twig-components', size: 'l');
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Frontend\Components\OPG\Account;

use OPG\Digideps\Frontend\Components\GOV\Form;
use OPG\Digideps\Frontend\Components\OPG\RichText\RichTextParser;
use OPG\Digideps\Frontend\Components\RenderableInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class Accept
{
    public Form $form;
    public RenderableInterface $content;

    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->content = new RichTextParser(
            $translator,
            linkResolver: fn (string $route) => array_fill(0, 2, $router->generate($route))
        )->parse('opg.account.accept.content', 'twig-components');
    }
}

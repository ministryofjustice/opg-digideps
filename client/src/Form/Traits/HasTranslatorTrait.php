<?php

namespace App\Form\Traits;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

trait HasTranslatorTrait
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     * @required
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    private function translate($id, $options, $domain): string
    {
        return $this->translator->trans($id, $options, $domain);
    }
}

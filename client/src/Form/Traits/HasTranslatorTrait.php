<?php

namespace App\Form\Traits;

use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

trait HasTranslatorTrait
{
    /**
     * @var Translator
     */
    private $translator;

    /**
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

<?php

namespace AppBundle\Form\Traits;

use Symfony\Component\Translation\TranslatorInterface;

trait HasTranslatorTrait
{
    /**
     * @var Translator
     */
    private $translator;

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    private function translate($id, $options, $domain)
    {
        return $this->translator->trans($id, $options, $domain);
    }
}

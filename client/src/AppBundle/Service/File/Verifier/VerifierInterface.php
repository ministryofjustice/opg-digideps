<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Form\Form;

interface VerifierInterface
{
    /**
     * @param Document $document
     * @param Form $form
     * @return bool
     */
    public function verify(Document $document, Form $form): bool;
}

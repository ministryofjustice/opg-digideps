<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Form\Form;

class CompositeVerifier implements VerifierInterface
{
    /** @var VerifierInterface[] */
    private $verifiers = [];

    /**
     * {@inheritDoc}
     */
    public function verify(Document $document, Form $form): bool
    {
        foreach ($this->verifiers as $verifier) {
            // Only add one constraint at a time so return on first error.
            if (false === $verifier->verify($document, $form)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param VerifierInterface $verifier
     * @return CompositeVerifier
     */
    public function addVerifier(VerifierInterface $verifier): CompositeVerifier
    {
        $this->verifiers[] = $verifier;

        return $this;
    }
}

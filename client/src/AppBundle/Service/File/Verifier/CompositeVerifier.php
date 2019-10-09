<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;

class CompositeVerifier implements VerifierInterface
{
    /** @var VerifierInterface[] */
    private $verifiers = [];

    /**
     * {@inheritDoc}
     */
    public function verify(Document $document, VerificationStatus $status): VerificationStatus
    {
        foreach ($this->verifiers as $verifier) {
            // Return on first error to prevent multiple form errors displaying for one file.
            if ($status->hasError()) {
                return $status;
            }

            $status = $verifier->verify($document, $status);
        }

        return $status;
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

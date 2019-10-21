<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;

interface VerifierInterface
{
    /**
     * @param Document $document
     * @param VerificationStatus $status
     * @return VerificationStatus
     */
    public function verify(Document $document, VerificationStatus $status): VerificationStatus;
}

<?php

namespace App\Service\File\Verifier;

use App\Entity\Report\Document;

interface VerifierInterface
{
    /**
     * @param Document $document
     * @param VerificationStatus $status
     * @return VerificationStatus
     */
    public function verify(Document $document, VerificationStatus $status): VerificationStatus;
}

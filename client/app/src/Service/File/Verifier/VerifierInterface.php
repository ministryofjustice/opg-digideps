<?php

namespace App\Service\File\Verifier;

use App\Entity\Report\Document;

interface VerifierInterface
{
    public function verify(Document $document, VerificationStatus $status): VerificationStatus;
}

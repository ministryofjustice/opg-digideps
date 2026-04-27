<?php

namespace OPG\Digideps\Frontend\Service\File\Verifier;

use OPG\Digideps\Frontend\Entity\Report\Document;

interface VerifierInterface
{
    public function verify(Document $document, VerificationStatus $status): VerificationStatus;
}

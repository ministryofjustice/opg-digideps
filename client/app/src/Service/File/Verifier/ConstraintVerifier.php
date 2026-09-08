<?php

namespace OPG\Digideps\Frontend\Service\File\Verifier;

use OPG\Digideps\Frontend\Entity\Report\Document;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintVerifier implements VerifierInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function verify(Document $document, VerificationStatus $status): VerificationStatus
    {
        $errors = $this->validator->validate($document, null, ['document']);

        if (count($errors) > 0) {
            $message = sprintf(
                '%s: %s',
                $document->getFile()?->getClientOriginalName() ?? 'unknown file name',
                $errors->offsetGet(0)->getMessage()
            );

            $status->addError($message);
        }

        return $status;
    }
}

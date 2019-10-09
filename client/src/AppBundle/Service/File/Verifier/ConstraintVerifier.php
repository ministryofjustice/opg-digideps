<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintVerifier implements VerifierInterface
{
    /** @var ValidatorInterface */
    private $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
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
                $document->getFile()->getClientOriginalName(),
                $errors->offsetGet(0)->getMessage()
            );

            $status->addError($message);
        }

        return $status;
    }
}

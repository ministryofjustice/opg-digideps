<?php

namespace App\Service\File\Verifier;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultiFileFormUploadVerifier
{
    /** @var VerifierInterface[] */
    private $verifiers;

    public function addVerifier(VerifierInterface $verifier): MultiFileFormUploadVerifier
    {
        $this->verifiers[] = $verifier;

        return $this;
    }

    public function verify(array $files, FormInterface $form, Report $report): bool
    {
        if (empty($files)) {
            $form->get('files')->addError(new FormError('No documents were uploaded'));

            return false;
        }

        foreach ($files as $file) {
            $status = $this->verifyFiles($report, $file);

            if (VerificationStatus::FAILED === $status->getStatus()) {
                $form->get('files')->addError(new FormError($status->getError()));
            }
        }

        return (0 === count($form->getErrors(true))) ? true : false;
    }

    private function verifyFiles(Report $report, UploadedFile $file): VerificationStatus
    {
        $document = (new Document())->setFile($file)->setReport($report);
        $status = new VerificationStatus();

        foreach ($this->verifiers as $verifier) {
            // Return on first error to prevent further validation.
            if (VerificationStatus::FAILED === $status->getStatus()) {
                return $status;
            }

            $status = $verifier->verify($document, $status);
        }

        return $status;
    }
}

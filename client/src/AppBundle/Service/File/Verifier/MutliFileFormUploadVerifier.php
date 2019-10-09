<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MutliFileFormUploadVerifier
{
    /** @var VerifierInterface[] */
    private $verifiers;

    /**
     * @param VerifierInterface $verifier
     * @return MutliFileFormUploadVerifier
     */
    public function addVerifier(VerifierInterface $verifier): MutliFileFormUploadVerifier
    {
        $this->verifiers[] = $verifier;

        return $this;
    }

    /**
     * @param array $files
     * @param FormInterface $form
     * @param Report $report
     * @return bool
     */
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

        return (count($form->getErrors(true)) === 0) ? true : false;
    }

    /**
     * @param Report $report
     * @param UploadedFile $file
     * @return VerificationStatus
     */
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

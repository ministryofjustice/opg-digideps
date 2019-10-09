<?php

namespace AppBundle\Service\File;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use AppBundle\Service\File\Verifier\VerificationStatus;
use AppBundle\Service\File\Verifier\VerifierInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

class MutliFileFormUploadHandler
{
    /** @var VerifierInterface */
    private $verifier;

    /**
     * @param VerifierInterface $verifier
     */
    public function __construct(VerifierInterface $verifier)
    {
        $this->verifier = $verifier;
    }

    /**
     * @param array $files
     * @param FormInterface $form
     * @param Report $report
     * @return array|null
     */
    public function handle(array $files, FormInterface $form, Report $report): ?array
    {
        if (empty($files)) {
            $form->get('files')->addError(new FormError('No documents were uploaded'));
            return null;
        }

        $documents = [];
        foreach ($files as $file) {
            $documents[] = $document = (new Document())->setFile($file)->setReport($report);

            $status = $this->verifier->verify($document, new VerificationStatus());

            if ($status->hasError()) {
                $form->get('files')->addError(new FormError($status->getError()));
            }
        }

        return $documents;
    }
}

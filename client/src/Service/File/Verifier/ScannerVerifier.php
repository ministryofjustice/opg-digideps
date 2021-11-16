<?php

namespace App\Service\File\Verifier;

use App\Entity\Report\Document;
use App\Service\File\Scanner\ClamFileScanner;
use App\Service\File\Scanner\Exception\VirusFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScannerVerifier implements VerifierInterface
{
    public function __construct(private ClamFileScanner $scanner, private TranslatorInterface $translator, private LoggerInterface $logger)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function verify(Document $document, VerificationStatus $status): VerificationStatus
    {
        try {
            $this->scanner->scanFile($document->getFile());
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());

            $message = sprintf(
                '%s: %s',
                $document->getFile()->getClientOriginalName(),
                $this->buildErrorMessage($e)
            );

            $status->addError($message);
        }

        return $status;
    }

    private function buildErrorMessage(\Throwable $e): string
    {
        $errorKey = ($e::class === VirusFoundException::class) ? 'virusFound' : 'generic';

        return $this
            ->translator
            ->trans("document.file.errors.{$errorKey}", [], 'validators');
    }
}

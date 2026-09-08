<?php

namespace OPG\Digideps\Frontend\Service\File\Verifier;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Service\File\Scanner\ClamFileScanner;
use OPG\Digideps\Frontend\Service\File\Scanner\Exception\VirusFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScannerVerifier implements VerifierInterface
{
    public function __construct(
        private readonly ClamFileScanner $scanner,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function verify(Document $document, VerificationStatus $status): VerificationStatus
    {
        try {
            $fileToVerify = $document->getFile();

            if ($fileToVerify === null) {
                throw new \DomainException('no file to verify');
            }

            $this->scanner->scanFile($fileToVerify);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());

            $message = sprintf(
                '%s: %s',
                $document->getFile()?->getClientOriginalName() ?? 'unknown file name',
                $this->buildErrorMessage($e)
            );

            $status->addError($message);
        }

        return $status;
    }

    private function buildErrorMessage(\Throwable $e): string
    {
        $errorKey = (get_class($e) === VirusFoundException::class) ? 'virusFound' : 'generic';

        return $this
            ->translator
            ->trans("document.file.errors.{$errorKey}", [], 'validators');
    }
}

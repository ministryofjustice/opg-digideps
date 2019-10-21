<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\File\Scanner\Exception\RiskyFileException;
use AppBundle\Service\File\Scanner\Exception\VirusFoundException;
use AppBundle\Service\File\Scanner\FileScanner;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\TranslatorInterface;

class ScannerVerifier implements VerifierInterface
{
    /** @var FileScanner */
    private $scanner;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Logger */
    private $logger;

    /**
     * @param FileScanner $scanner
     * @param TranslatorInterface $translator
     * @param Logger $logger
     */
    public function __construct(FileScanner $scanner, TranslatorInterface $translator, Logger $logger)
    {
        $this->scanner = $scanner;
        $this->translator = $translator;
        $this->logger = $logger;
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
            $status->addError($this->buildErrorMessage($e));
        }

        return $status;
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    private function buildErrorMessage(\Throwable $e): string
    {
        $errorKey = $this->determineErrorType($e);

        return $this
            ->translator
            ->trans("document.file.errors.{$errorKey}", [], 'validators');
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    private function determineErrorType(\Throwable $e): string
    {
        switch (get_class($e)) {
            case RiskyFileException::class:
                return 'risky';
            case VirusFoundException::class:
                return 'virusFound';
            default:
                return 'generic';
        }
    }
}

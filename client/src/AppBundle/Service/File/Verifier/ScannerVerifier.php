<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\File\Scanner\ClamFileScanner;
use AppBundle\Service\File\Scanner\Exception\VirusFoundException;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Translation\TranslatorInterface;

class ScannerVerifier implements VerifierInterface
{
    /** @var ClamFileScanner */
    private $scanner;

    /** @var TranslatorInterface */
    private $translator;

    /** @var Logger */
    private $logger;

    /**
     * @param ClamFileScanner $scanner
     * @param TranslatorInterface $translator
     * @param Logger $logger
     */
    public function __construct(ClamFileScanner $scanner, TranslatorInterface $translator, Logger $logger)
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

            $message = sprintf(
                '%s: %s',
                $document->getFile()->getClientOriginalName(),
                $this->buildErrorMessage($e)
            );

            $status->addError($message);
        }

        return $status;
    }

    /**
     * @param \Throwable $e
     * @return string
     */
    private function buildErrorMessage(\Throwable $e): string
    {
        $errorKey = (get_class($e) === VirusFoundException::class) ? 'virusFound' : 'generic';

        return $this
            ->translator
            ->trans("document.file.errors.{$errorKey}", [], 'validators');
    }
}

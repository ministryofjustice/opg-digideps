<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service\File\Verifier;

use OPG\Digideps\Frontend\Entity\Report\Document;
use OPG\Digideps\Frontend\Service\File\Scanner\ClamFileScanner;
use OPG\Digideps\Frontend\Service\File\Verifier\ScannerVerifier;
use OPG\Digideps\Frontend\Service\File\Verifier\VerificationStatus;
use OPG\Digideps\Frontend\Service\File\Verifier\VerifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScannerVerifierTest extends TestCase
{
    private VerifierInterface $verifier;
    private ClamFileScanner&MockObject $scanner;
    private TranslatorInterface&MockObject $translator;
    private LoggerInterface&MockObject $logger;
    private Document $document;
    private VerificationStatus $result;

    public function setUp(): void
    {
        $this->scanner = $this->getMockBuilder(ClamFileScanner::class)->disableOriginalConstructor()->getMock();
        $this->translator = self::createMock(TranslatorInterface::class);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->verifier = new ScannerVerifier($this->scanner, $this->translator, $this->logger);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $this->document = new Document()->setFile($file);
    }

    public function testVerificationPassesWhenGivenValidDocument(): void
    {
        $this->ensureDocumentWillBeValid()
            ->invokeTest()
            ->assertStatusIsPassed();
    }

    public function testReturnsFalseWhenGivenInvalidDocument(): void
    {
        $this->ensureDocumentWillBeInvalid()
            ->ensureErrorWillBeTranslated()
            ->invokeTest()
            ->assertStatusIsFailed();
    }

    private function ensureDocumentWillBeValid(): static
    {
        $this->scanner->expects(self::once())
            ->method('scanFile')
            ->with($this->document->getFile());

        return $this;
    }

    private function ensureDocumentWillBeInvalid(): static
    {
        $this->scanner->expects(self::once())
            ->method('scanFile')
            ->with($this->document->getFile())
            ->willThrowException(new \Exception());

        return $this;
    }

    private function ensureErrorWillBeTranslated(): ScannerVerifierTest
    {
        $this->translator->method('trans')->willReturn('error message');

        return $this;
    }

    private function invokeTest(): ScannerVerifierTest
    {
        $this->result = $this->verifier->verify($this->document, new VerificationStatus());

        return $this;
    }

    private function assertStatusIsPassed(): void
    {
        self::assertEquals(VerificationStatus::PASSED, $this->result->getStatus());
        self::assertNull($this->result->getError());
    }

    private function assertStatusIsFailed(): void
    {
        self::assertEquals(VerificationStatus::FAILED, $this->result->getStatus());
        self::assertNotNull($this->result->getError());
    }
}

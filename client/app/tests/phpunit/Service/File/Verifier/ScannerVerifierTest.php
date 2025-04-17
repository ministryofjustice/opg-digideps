<?php

namespace App\Service\File\Verifier;

use App\Entity\Report\Document;
use App\Service\File\Scanner\ClamFileScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class ScannerVerifierTest extends TestCase
{
    /** @var VerifierInterface */
    private $verifier;

    /** @var ClamFileScanner|MockObject */
    private $scanner;

    /** @var TranslatorInterface|MockObject */
    private $translator;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var Document */
    private $document;

    /** @var Form|MockObject */
    private $form;

    /** @var bool */
    private $result;

    public function setUp(): void
    {
        $this->scanner = $this->getMockBuilder(ClamFileScanner::class)->disableOriginalConstructor()->getMock();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->verifier = new ScannerVerifier($this->scanner, $this->translator, $this->logger);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $this->document = (new Document())->setFile($file);
    }

    /**
     * @test
     */
    public function verificationPassesWhenGivenValidDocument()
    {
        $this
            ->ensureDocumentWillBeValid()
            ->invokeTest()
            ->assertStatusIsPassed();
    }

    /**
     * @test
     */
    public function returnsFalseWhenGivenInvalidDocument()
    {
        $this
            ->ensureDocumentWillBeInvalid()
            ->ensureErrorWillBeTranslated()
            ->invokeTest()
            ->assertStatusIsFailed();
    }

    private function ensureDocumentWillBeValid(): ScannerVerifierTest
    {
        $this
            ->scanner
            ->expects($this->once())
            ->method('scanFile')
            ->with($this->document->getFile());

        return $this;
    }

    private function ensureDocumentWillBeInvalid(): ScannerVerifierTest
    {
        $this
            ->scanner
            ->expects($this->once())
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
        $this->assertEquals(VerificationStatus::PASSED, $this->result->getStatus());
        $this->assertNull($this->result->getError());
    }

    private function assertStatusIsFailed(): void
    {
        $this->assertEquals(VerificationStatus::FAILED, $this->result->getStatus());
        $this->assertNotNull($this->result->getError());
    }
}

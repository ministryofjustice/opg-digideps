<?php

namespace AppBundle\Service\File\Verifier;

use AppBundle\Entity\Report\Document;
use AppBundle\Service\File\Scanner\FileScanner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Translation\TranslatorInterface;

class ScannerVerifierTest extends TestCase
{
    /** @var VerifierInterface */
    private $verifier;

    /** @var FileScanner | MockObject */
    private $scanner;

    /** @var TranslatorInterface | MockObject */
    private $translator;

    /** @var Logger | MockObject */
    private $logger;

    /** @var Document */
    private $document;

    /** @var Form | MockObject */
    private $form;

    /** @var bool */
    private $result;

    public function setUp(): void
    {
        $this->scanner = $this->getMockBuilder(FileScanner::class)->disableOriginalConstructor()->getMock();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->logger = $this->getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $this->verifier = new ScannerVerifier($this->scanner, $this->translator, $this->logger);

        $file = $this->getMockBuilder(UploadedFile::class)->disableOriginalConstructor()->getMock();
        $file->method('getClientOriginalName')->willReturn('file.txt');
        $this->document = (new Document())->setFile($file);
        $this->form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @test
     */
    public function returnsTrueWhenGivenValidDocument()
    {
        $this
            ->ensureDocumentWillBeValid()
            ->invokeTest()
            ->assertResultIsTrue();
    }

    /**
     * @test
     */
    public function returnsFalseWhenGivenInvalidDocument()
    {
        $this
            ->ensureDocumentWillBeInvalid()
            ->assertErrorsWillBeAddedToForm()
            ->invokeTest()
            ->assertResultIsFalse();
    }

    /**
     * @return ScannerVerifierTest
     */
    private function ensureDocumentWillBeValid(): ScannerVerifierTest
    {
        $this
            ->scanner
            ->expects($this->once())
            ->method('scanFile')
            ->with($this->document->getFile());

        return $this;
    }

    /**
     * @return ScannerVerifierTest
     */
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

    /**
     * @return ConstraintVerifierTest
     */
    private function assertErrorsWillBeAddedToForm(): ScannerVerifierTest
    {
        $this->translator->method('trans')->willReturn('error message');
        $childForm = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();

        $this->form->method('get')->with('files')->willReturn($childForm);
        $childForm->expects($this->once())->method('addError')->with($this->isInstanceOf(FormError::class));

        return $this;
    }

    /**
     * @return ScannerVerifierTest
     */
    private function invokeTest(): ScannerVerifierTest
    {
        $this->result = $this->verifier->verify($this->document, $this->form);

        return $this;
    }

    private function assertResultIsTrue(): void
    {
        $this->assertTrue($this->result);
    }

    private function assertResultIsFalse(): void
    {
        $this->assertFalse($this->result);
    }
}

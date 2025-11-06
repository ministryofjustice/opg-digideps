<?php

declare(strict_types=1);

namespace Tests\App\Entity;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DocumentTest extends TestCase
{
    public function testIsValidForReportNoUploadedFile(): void
    {
        $mockCtx = self::createMock(ExecutionContext::class);
        $mockCtx->expects(self::never())->method('buildViolation');

        $doc = new Document();

        $doc->isValidForReport($mockCtx);
    }

    public function testIsValidForReportFilenameTooLong(): void
    {
        $mockFile = self::createMock(UploadedFile::class);
        $mockFile->expects(self::once())
            ->method('getClientOriginalName')
            ->willReturn(str_repeat('a', Document::FILE_NAME_MAX_LENGTH + 1));

        // expect max file name length exceeded violation
        $mockConstraintBuilder = self::createMock(ConstraintViolationBuilderInterface::class);
        $mockConstraintBuilder->expects(self::once())->method('atPath')->with('file')->willReturnSelf();
        $mockConstraintBuilder->expects(self::once())->method('addViolation')->willReturnSelf();

        $mockCtx = self::createMock(ExecutionContext::class);
        $mockCtx->expects(self::once())
            ->method('buildViolation')
            ->with('document.file.errors.maxMessage')
            ->willReturn($mockConstraintBuilder);

        $doc = new Document();
        $doc->setFile($mockFile);

        $doc->isValidForReport($mockCtx);
    }

    public function testIsValidForReportFilenameAlreadyExists(): void
    {
        $duplicatedFileName = 'a_duplicate_file';

        $mockFile = self::createMock(UploadedFile::class);
        $mockFile->expects(self::once())->method('getClientOriginalName')->willReturn($duplicatedFileName);

        // document whose filename matches the filename of the uploaded file
        $mockDoc = self::createMock(Document::class);
        $mockDoc->expects(self::once())->method('getFileName')->willReturn($duplicatedFileName);

        $mockReport = self::createMock(Report::class);
        $mockReport->expects(self::once())->method('getDocuments')->willReturn([$mockDoc]);

        // expect duplicate filename violation to be added to context
        $mockConstraintBuilder = self::createMock(ConstraintViolationBuilderInterface::class);
        $mockConstraintBuilder->expects(self::once())->method('atPath')->with('file')->willReturnSelf();
        $mockConstraintBuilder->expects(self::once())->method('addViolation')->willReturnSelf();

        $mockCtx = self::createMock(ExecutionContext::class);
        $mockCtx->expects(self::once())
            ->method('buildViolation')
            ->with('document.file.errors.alreadyPresent')
            ->willReturn($mockConstraintBuilder);

        $doc = new Document();
        $doc->setFile($mockFile);
        $doc->setReport($mockReport);

        $doc->isValidForReport($mockCtx);
    }
}

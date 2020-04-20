<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;


use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentHelpers
{
    public function generateReportPdfDocument(Report $report, int $documentId = 6789, string $storageReference = 'test')
    {
        $document = new Document($report);

        return $document
            ->setStorageReference($storageReference)
            ->setFileName('test.pdf')
            ->setId($documentId);
    }

    public function generateSupportingDocument(Report $report, int $documentId = 6789, string $storageReference = 'test')
    {
        return ($this->generateReportPdfDocument($report, $documentId, $storageReference))->setIsReportPdf(false);
    }
}

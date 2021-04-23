<?php declare(strict_types=1);


namespace App\TestHelpers;

use App\Entity\Report\Document;
use App\Entity\Report\Report;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentHelpers
{
    public static function createReportPdfDocument()
    {
        $faker = Factory::create('GB_en');

        $report = ReportHelpers::createReport();
        $document = new Document($report);

        return $document
            ->setStorageReference($faker->url)
            ->setFileName('report.pdf')
            ->setId(1);
    }

    public static function createSupportingDocument()
    {
        return (self::createReportPdfDocument())
            ->setIsReportPdf(false)
            ->setFileName('supporting-document.pdf');
    }
}

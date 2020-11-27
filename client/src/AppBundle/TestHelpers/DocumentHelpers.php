<?php declare(strict_types=1);


namespace AppBundle\TestHelpers;

use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\Report;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentHelpers
{
    public static function generateReportPdfDocument()
    {
        $faker = Factory::create('GB_en');

        $report = ReportHelpers::createReport();
        $document = new Document($report);

        return $document
            ->setStorageReference($faker->url)
            ->setFileName('report.pdf')
            ->setId(1);
    }

    public static function generateSupportingDocument()
    {
        return (self::generateReportPdfDocument())
            ->setIsReportPdf(false)
            ->setFileName('supporting-document.pdf');
    }
}

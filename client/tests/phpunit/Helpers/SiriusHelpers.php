<?php declare(strict_types=1);

namespace DigidepsTests\Helpers;

use App\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use App\Model\Sirius\SiriusDocumentFile;
use App\Model\Sirius\SiriusDocumentUpload;
use App\Model\Sirius\SiriusReportPdfDocumentMetadata;
use App\Model\Sirius\SiriusSupportingDocumentMetadata;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusHelpers extends KernelTestCase
{
    public static function generateSiriusReportPdfDocumentUpload(
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $orderType,
        int $submissionId,
        string $fileName,
        ?string $fileContents,
        ?string $s3Reference
    ) {
        $siriusReportPdfDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
            ->setReportingPeriodFrom($startDate)
            ->setReportingPeriodTo($endDate)
            ->setYear(2018)
            ->setDateSubmitted($submittedDate)
            ->setType($orderType)
            ->setSubmissionId($submissionId);

        $file = (new SiriusDocumentFile())
            ->setName($fileName)
            ->setMimetype('application/pdf');

        if (!is_null($fileContents)) {
            $file->setSource(base64_encode($fileContents));
        }

        if (!is_null($s3Reference)) {
            $file->setS3Reference($s3Reference);
        }

        return (new SiriusDocumentUpload())
            ->setType('reports')
            ->setAttributes($siriusReportPdfDocumentMetadata)
            ->setFile($file);
    }

    public static function generateSiriusSupportingDocumentUpload(int $submissionId, string $fileName, ?string $fileContents, ?string $s3Reference)
    {
        $siriusSupportingDocumentMetadata = (new SiriusSupportingDocumentMetadata())
            ->setSubmissionId($submissionId);

        $file = (new SiriusDocumentFile())
            ->setName($fileName)
            ->setMimetype('application/pdf');

        if (!is_null($fileContents)) {
            $file->setSource(base64_encode($fileContents));
        }

        if (!is_null($s3Reference)) {
            $file->setS3Reference($s3Reference);
        }

        return (new SiriusDocumentUpload())
            ->setType('supportingdocuments')
            ->setAttributes($siriusSupportingDocumentMetadata)
            ->setFile($file);
    }

    public static function generateSiriusChecklistPdfUpload(
        string $fileName,
        string $fileContents,
        int $submissionId,
        string $submitterEmail,
        DateTime $reportingPeriodFrom,
        DateTime $reportingPeriodTo,
        int $year,
        string $type
    ) {
        $file = (new SiriusDocumentFile())
            ->setName($fileName)
            ->setMimetype('application/pdf')
            ->setSource(base64_encode($fileContents));

        $attributes = (new SiriusChecklistPdfDocumentMetadata())
            ->setSubmissionId($submissionId)
            ->setSubmitterEmail($submitterEmail)
            ->setReportingPeriodFrom($reportingPeriodFrom)
            ->setReportingPeriodTo($reportingPeriodTo)
            ->setYear($year)
            ->setType($type);

        return (new SiriusDocumentUpload())
            ->setType('checklists')
            ->setAttributes($attributes)
            ->setFile($file);
    }
}

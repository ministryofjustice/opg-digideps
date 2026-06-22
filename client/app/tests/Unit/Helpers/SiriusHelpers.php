<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Helpers;

use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusChecklistPdfDocumentMetadata;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentFile;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusDocumentUpload;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusReportPdfDocumentMetadata;
use OPG\Digideps\Frontend\Sync\Model\Sirius\SiriusSupportingDocumentMetadata;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusHelpers extends KernelTestCase
{
    /**
     * @param string[] $courtOrderUids
     */
    public static function generateSiriusReportPdfDocumentUpload(
        \DateTime $startDate,
        \DateTime $endDate,
        \DateTime $submittedDate,
        string $orderType,
        int $submissionId,
        string $fileName,
        ?string $fileContents,
        ?string $s3Reference,
        ?string $digidepsReportType,
        array $courtOrderUids = [],
    ): SiriusDocumentUpload {
        $siriusReportPdfDocumentMetadata = new SiriusReportPdfDocumentMetadata();
        $siriusReportPdfDocumentMetadata->reportingPeriodFrom = $startDate;
        $siriusReportPdfDocumentMetadata->reportingPeriodTo = $endDate;
        $siriusReportPdfDocumentMetadata->year = 2018;
        $siriusReportPdfDocumentMetadata->dateSubmitted = $submittedDate;
        $siriusReportPdfDocumentMetadata->type = $orderType;
        $siriusReportPdfDocumentMetadata->submissionId = $submissionId;
        $siriusReportPdfDocumentMetadata->digidepsReportType = $digidepsReportType;
        $siriusReportPdfDocumentMetadata->courtOrderUids = $courtOrderUids;

        $file = new SiriusDocumentFile()
            ->setName($fileName)
            ->setMimetype('application/pdf');

        if (!is_null($fileContents)) {
            $file->setSource(base64_encode($fileContents));
        }

        if (!is_null($s3Reference)) {
            $file->setS3Reference($s3Reference);
        }

        return new SiriusDocumentUpload()
            ->setType('reports')
            ->setAttributes($siriusReportPdfDocumentMetadata)
            ->setFile($file);
    }

    public static function generateSiriusSupportingDocumentUpload(
        int $submissionId,
        string $fileName,
        ?string $fileContents,
        ?string $s3Reference,
    ): SiriusDocumentUpload {
        $siriusSupportingDocumentMetadata = new SiriusSupportingDocumentMetadata();
        $siriusSupportingDocumentMetadata->submissionId = $submissionId;

        $file = new SiriusDocumentFile()
            ->setName($fileName)
            ->setMimetype('application/pdf');

        if (!is_null($fileContents)) {
            $file->setSource(base64_encode($fileContents));
        }

        if (!is_null($s3Reference)) {
            $file->setS3Reference($s3Reference);
        }

        return new SiriusDocumentUpload()
            ->setType('supportingdocuments')
            ->setAttributes($siriusSupportingDocumentMetadata)
            ->setFile($file);
    }

    public static function generateSiriusChecklistPdfUpload(
        string $fileName,
        string $fileContents,
        int $submissionId,
        string $submitterEmail,
        \DateTime $reportingPeriodFrom,
        \DateTime $reportingPeriodTo,
        int $year,
        string $type
    ): SiriusDocumentUpload {
        $file = new SiriusDocumentFile()
            ->setName($fileName)
            ->setMimetype('application/pdf')
            ->setSource(base64_encode($fileContents));

        $attributes = new SiriusChecklistPdfDocumentMetadata()
            ->setSubmissionId($submissionId)
            ->setSubmitterEmail($submitterEmail)
            ->setReportingPeriodFrom($reportingPeriodFrom)
            ->setReportingPeriodTo($reportingPeriodTo)
            ->setYear($year)
            ->setType($type);

        return new SiriusDocumentUpload()
            ->setType('checklists')
            ->setAttributes($attributes)
            ->setFile($file);
    }
}

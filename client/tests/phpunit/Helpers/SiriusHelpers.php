<?php declare(strict_types=1);

namespace DigidepsTests\Helpers;

use AppBundle\Model\Sirius\SiriusDocumentFile;
use AppBundle\Model\Sirius\SiriusDocumentUpload;
use AppBundle\Model\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Model\Sirius\SiriusSupportingDocumentMetadata;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusHelpers extends KernelTestCase
{
    static public function generateSiriusReportPdfDocumentUpload(
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $orderType,
        int $submissionId,
        string $fileName,
        string $fileContents
    )
    {
        $siriusReportPdfDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
            ->setReportingPeriodFrom($startDate)
            ->setReportingPeriodTo($endDate)
            ->setYear('2018')
            ->setDateSubmitted($submittedDate)
            ->setType($orderType)
            ->setSubmissionId($submissionId);

        $file = (new SiriusDocumentFile())
            ->setName($fileName)
            ->setMimetype('application/pdf')
            ->setSource(base64_encode($fileContents));

        return (new SiriusDocumentUpload())
            ->setType('reports')
            ->setAttributes($siriusReportPdfDocumentMetadata)
            ->setFile($file);
    }

    static public function generateSiriusSupportingDocumentUpload(int $submissionId)
    {
        $siriusSupportingDocumentMetadata = (new SiriusSupportingDocumentMetadata())
            ->setSubmissionId($submissionId);

        $file = (new SiriusDocumentFile())
            ->setName('bank-statement-March.pdf')
            ->setMimetype('application/pdf')
            ->setSource('dGVzdA==');

        return (new SiriusDocumentUpload())
            ->setType('supportingdocument')
            ->setAttributes($siriusSupportingDocumentMetadata)
            ->setFile($file);
    }
}

<?php declare(strict_types=1);

namespace DigidepsTests\Helpers;


use AppBundle\Service\Client\Sirius\SiriusReportPdfDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use AppBundle\Service\Client\Sirius\SiriusSupportingDocumentMetadata;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusHelpers extends KernelTestCase
{
    static public function generateSiriusReportPdfDocumentUpload(
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $orderType,
        int $submissionId
    )
    {
        $siriusReportPdfDocumentMetadata = (new SiriusReportPdfDocumentMetadata())
            ->setReportingPeriodFrom($startDate)
            ->setReportingPeriodTo($endDate)
            ->setYear('2018')
            ->setDateSubmitted($submittedDate)
            ->setType($orderType)
            ->setSubmissionId($submissionId);

        return (new SiriusDocumentUpload())
            ->setType('reports')
            ->setAttributes($siriusReportPdfDocumentMetadata);
    }

    static public function generateSiriusSupportingDocumentUpload(int $submissionId)
    {
        $siriusSupportingDocumentMetadata = (new SiriusSupportingDocumentMetadata())
            ->setSubmissionId($submissionId);

        return (new SiriusDocumentUpload())
            ->setType('supportingdocument')
            ->setAttributes($siriusSupportingDocumentMetadata);
    }
}

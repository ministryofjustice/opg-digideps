<?php declare(strict_types=1);

namespace DigidepsTests\Helpers;


use AppBundle\Service\Client\Sirius\SiriusDocumentMetadata;
use AppBundle\Service\Client\Sirius\SiriusDocumentUpload;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SiriusHelpers extends KernelTestCase
{
    static public function generateSiriusDocumentUpload(
        DateTime $startDate,
        DateTime $endDate,
        DateTime $submittedDate,
        string $orderType
    )
    {
        $siriusDocumentMetadata = (new SiriusDocumentMetadata())
            ->setReportingPeriodFrom($startDate)
            ->setReportingPeriodTo($endDate)
            ->setYear('2018')
            ->setDateSubmitted($submittedDate)
            ->setOrderType($orderType);

        return (new SiriusDocumentUpload())
            ->setType('reports')
            ->setAttributes($siriusDocumentMetadata);
    }
}

<?php declare(strict_types=1);


use App\Model\Sirius\QueuedDocumentData;
use PHPUnit\Framework\TestCase;

class QueuedDocumentDataTest extends TestCase
{
    /**
     * @dataProvider supportingDocumentProvider
     * @test
     */
    public function supportingDocumentCanBeSynced(?string $uuid, bool $expectedResult)
    {
        $supportingDocument = (new QueuedDocumentData())
            ->setIsReportPdf(false)
            ->setReportSubmissionUuid($uuid);

        self::assertEquals($expectedResult, $supportingDocument->supportingDocumentCanBeSynced());
    }

    public function supportingDocumentProvider()
    {
        return [
            'Can be synced' => ['abc-123-def-456', true],
            'Cannot be synced' => [null, false]
        ];
    }
}

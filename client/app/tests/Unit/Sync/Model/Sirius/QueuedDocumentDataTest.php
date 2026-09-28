<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Sync\Model\Sirius;

use OPG\Digideps\Frontend\Sync\Model\Sirius\QueuedDocumentData;
use PHPUnit\Framework\TestCase;

class QueuedDocumentDataTest extends TestCase
{
    /**
     * @dataProvider supportingDocumentProvider
     */
    public function testSupportingDocumentCanBeSynced(?string $uuid, bool $expectedResult): void
    {
        $supportingDocument = new QueuedDocumentData()
            ->setIsReportPdf(false)
            ->setReportSubmissionUuid($uuid);

        self::assertEquals($expectedResult, $supportingDocument->supportingDocumentCanBeSynced());
    }

    public static function supportingDocumentProvider(): array
    {
        return [
            'Can be synced' => ['abc-123-def-456', true],
            'Cannot be synced' => [null, false]
        ];
    }
}

<?php

declare(strict_types=1);

namespace app\tests\Integration\Model;

use App\Entity\StagingSelectedCandidate;
use App\Model\QueryPager;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

class QueryPagerIntegrationIntegrationTest extends ApiIntegrationTestCase
{
    public function testGetRows(): void
    {
        $courtOrderUid = '99775533';
        $numRowsExpected = 20;

        for ($i = 0; $i < $numRowsExpected; ++$i) {
            $candidate = new StagingSelectedCandidate(DeputyshipCandidateAction::InsertOrder, $courtOrderUid);
            self::$entityManager->persist($candidate);
        }

        self::$entityManager->flush();

        $pageQueryBuilder = self::$entityManager->createQueryBuilder()
            ->select('ssc')
            ->from(StagingSelectedCandidate::class, 'ssc')
            ->orderBy('ssc.id', 'ASC');

        $sut = new QueryPager($pageQueryBuilder);

        // test getting rows as objects
        $generator = $sut->getRows(pageSize: 3, asArray: false);

        $numRowsActual = 0;

        /** @var StagingSelectedCandidate $candidate */
        foreach ($generator as $candidate) {
            ++$numRowsActual;
            self::assertEquals(DeputyshipCandidateAction::InsertOrder, $candidate->action);
            self::assertEquals($courtOrderUid, $candidate->orderUid);
        }

        self::assertEquals($numRowsExpected, $numRowsActual);

        // test getting rows as arrays
        $generator = $sut->getRows(pageSize: 3);

        $numRowsActual = 0;

        foreach ($generator as $candidate) {
            ++$numRowsActual;
            self::assertEquals(DeputyshipCandidateAction::InsertOrder, $candidate['action']);
            self::assertEquals($courtOrderUid, $candidate['orderUid']);
        }

        self::assertEquals($numRowsExpected, $numRowsActual);
    }
}

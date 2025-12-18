<?php

declare(strict_types=1);

namespace app\tests\Integration\Model;

use App\Entity\StagingSelectedCandidate;
use App\Model\QueryPager;
use App\Tests\Integration\ApiIntegrationTestCase;
use App\v2\Registration\Enum\DeputyshipCandidateAction;

class QueryPagerIntegrationTest extends ApiIntegrationTestCase
{
    public function testGetRows(): void
    {
        $courtOrderUid = '99775533';

        // these variables are intended to test behaviour at boundaries:
        // number of rows in database > limit, and not a multiple of page size;
        // limit is not divisible by page size
        $limit = 175;
        $numRowsInDatabase = 190;
        $pageSize = 11;

        for ($i = 0; $i < $numRowsInDatabase; ++$i) {
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
        $generator = $sut->getRows(pageSize: $pageSize, asArray: false, limit: $limit);

        $numRowsActual = 0;
        $candidateIds = [];

        /** @var StagingSelectedCandidate $candidate */
        foreach ($generator as $candidate) {
            ++$numRowsActual;
            $candidateIds[] = $candidate->id;
            self::assertEquals(DeputyshipCandidateAction::InsertOrder, $candidate->action);
            self::assertEquals($courtOrderUid, $candidate->orderUid);
        }

        self::assertEquals($limit, $numRowsActual);
        self::assertEquals($limit, count(array_unique($candidateIds)));

        // test getting rows as arrays
        $generator = $sut->getRows(pageSize: $pageSize, limit: $limit);

        $numRowsActual = 0;
        $candidateIds = [];

        foreach ($generator as $candidate) {
            ++$numRowsActual;
            $candidateIds[] = $candidate['id'];
            self::assertEquals(DeputyshipCandidateAction::InsertOrder, $candidate['action']);
            self::assertEquals($courtOrderUid, $candidate['orderUid']);
        }

        self::assertEquals($limit, $numRowsActual);
        self::assertEquals($limit, count(array_unique($candidateIds)));

        // test getting rows without a limit (should get all rows)
        $rows = iterator_to_array($sut->getRows(pageSize: $pageSize));
        self::assertCount($numRowsInDatabase, $rows);
    }
}

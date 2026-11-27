<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Entity;

use JMS\Serializer\Exception\RuntimeException as SerializerRuntimeException;
use JMS\Serializer\SerializerBuilder;
use OPG\Digideps\Frontend\Entity\CourtOrder;
use OPG\Digideps\Frontend\Entity\Deputy;
use OPG\Digideps\Frontend\Entity\Report\Report;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CourtOrderTest extends TestCase
{
    private CourtOrder $courtOrder;

    public function setUp(): void
    {
        $this->courtOrder = new CourtOrder();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function orderMadeDateFormatsProvider(): iterable
    {
        yield 'v2 datetime' => ['2025-03-04 10:15:30', '2025-03-04 10:15:30+00:00'];
        yield 'legacy datetime in UTC' => ['2025-03-04T10:15:30+00:00', '2025-03-04 10:15:30+00:00'];
        yield 'legacy datetime with offset' => ['2025-03-04T10:15:30+05:30', '2025-03-04 10:15:30+05:30'];
        yield 'legacy datetime with negative offset' => ['2025-03-04T10:15:30-04:00', '2025-03-04 10:15:30-04:00'];
        yield 'legacy datetime with Z timezone' => ['2025-03-04T10:15:30Z', '2025-03-04 10:15:30+00:00'];
    }

    #[DataProvider('orderMadeDateFormatsProvider')]
    public function testDeserializesOrderMadeDateFromBothApiFormats(string $value, string $expected): void
    {
        $serializer = SerializerBuilder::create()->build();
        $courtOrder = $serializer->deserialize(
            json_encode(['order_made_date' => $value], JSON_THROW_ON_ERROR),
            CourtOrder::class,
            'json'
        );

        self::assertInstanceOf(CourtOrder::class, $courtOrder);
        self::assertNotNull($courtOrder->orderMadeDate);
        self::assertSame($expected, $courtOrder->orderMadeDate->format('Y-m-d H:i:sP'));
    }

    public function testDeserializesLegacyCourtOrdersInReport(): void
    {
        $serializer = SerializerBuilder::create()->build();
        $report = $serializer->deserialize(json_encode([
            'court_orders' => [
                [
                    'court_order_uid' => 'PFA-123',
                    'order_made_date' => '2025-03-04T10:15:30+00:00',
                ],
                [
                    'court_order_uid' => 'HW-456',
                    'order_made_date' => '2025-04-05T11:16:31+01:00',
                ],
            ],
        ], JSON_THROW_ON_ERROR), Report::class, 'json');

        self::assertInstanceOf(Report::class, $report);
        $courtOrders = $report->getCourtOrders();
        self::assertCount(2, $courtOrders);
        self::assertSame('PFA-123', $courtOrders[0]->getCourtOrderUid());
        self::assertSame('2025-03-04T10:15:30+00:00', $courtOrders[0]->orderMadeDate?->format(\DateTime::ATOM));
        self::assertSame('HW-456', $courtOrders[1]->getCourtOrderUid());
        self::assertSame('2025-04-05T11:16:31+01:00', $courtOrders[1]->orderMadeDate?->format(\DateTime::ATOM));
    }

    public function testNullOrderMadeDateRemainsNull(): void
    {
        $serializer = SerializerBuilder::create()->build();
        $courtOrder = $serializer->deserialize('{"order_made_date":null}', CourtOrder::class, 'json');

        self::assertInstanceOf(CourtOrder::class, $courtOrder);
        self::assertNull($courtOrder->orderMadeDate);
    }

    public function testInvalidOrderMadeDateIsRejected(): void
    {
        $serializer = SerializerBuilder::create()->build();

        $this->expectException(SerializerRuntimeException::class);
        $serializer->deserialize('{"order_made_date":"not a date"}', CourtOrder::class, 'json');
    }

    public function testSerializesOrderMadeDateInV2Format(): void
    {
        $serializer = SerializerBuilder::create()->build();
        $this->courtOrder->orderMadeDate = new \DateTime('2025-03-04T10:15:30+05:30');
        $data = json_decode($serializer->serialize($this->courtOrder, 'json'), true, flags: JSON_THROW_ON_ERROR);

        self::assertIsArray($data);
        self::assertArrayHasKey('order_made_date', $data);
        self::assertSame('2025-03-04 10:15:30', $data['order_made_date']);
    }

    public static function inactiveReportProvider(): array
    {
        return [
            'No reports' => [],
            'Submitted report' => [new Report()->setSubmitted(true)],
            'Unsubmitted report' => [new Report()->setSubmitted(true)->setUnSubmitDate(new \DateTime())],
        ];
    }

    #[DataProvider('inactiveReportProvider')]
    public function testGetActiveReportNull(?Report $report = null): void
    {
        $this->courtOrder->setReports($report ? [$report] : []);

        $result = $this->courtOrder->getActiveReport();

        self::assertNull($result);
    }

    public function testGetActiveReportsOneActiveReport(): void
    {
        $report = new Report();
        $this->courtOrder->setReports([$report]);

        $result = $this->courtOrder->getActiveReport();

        self::assertSame($report, $result);
    }

    public function testGetFirstActiveReport(): void
    {
        $inactiveReport = new Report()->setSubmitted(true);
        $activeReport1 = new Report();
        $activeReport2 = new Report();

        $this->courtOrder->setReports([
            $inactiveReport,
            $activeReport1,
            $activeReport2,
        ]);

        $result = $this->courtOrder->getActiveReport();

        self::assertSame($activeReport1, $result);
    }

    public static function unsubmittedReportProvider(): array
    {
        return [
            'No reports' => [],
            'Submitted report' => [new Report()->setSubmitted(true)],
            'Report submitted and unsubmitted date set' => [new Report()->setSubmitted(true)->setUnSubmitDate(new \DateTime())],
        ];
    }

    #[DataProvider('unsubmittedReportProvider')]
    public function testGetUnsubmittedReportNull(?Report $report = null): void
    {
        $this->courtOrder->setReports($report ? [$report] : []);

        $result = $this->courtOrder->getUnsubmittedReport();

        self::assertNull($result);
    }

    public function testGetUnsubmittedReportOneUnsubmittedReport(): void
    {
        $report = new Report()->setSubmitted(false)->setUnSubmitDate(new \DateTime());
        $this->courtOrder->setReports([$report]);

        $result = $this->courtOrder->getUnsubmittedReport();

        self::assertSame($report, $result);
    }

    public function testGetFirstUnsubmittedReport(): void
    {
        $activeReport = new Report();
        $unsubmittedReport1 = new Report()->setSubmitted(false)->setUnSubmitDate(new \DateTime());
        $unsubmittedReport2 = new Report()->setSubmitted(false)->setUnSubmitDate(new \DateTime());

        $this->courtOrder->setReports([
            $activeReport,
            $unsubmittedReport1,
            $unsubmittedReport2,
        ]);

        $result = $this->courtOrder->getUnsubmittedReport();

        self::assertSame($unsubmittedReport1, $result);
    }

    public function testGetSubmittedReportsNoSubmittedReports(): void
    {
        $result = $this->courtOrder->getSubmittedReports();

        self::assertEquals([], $result);
    }

    public function testGetSubmittedReportsOnlySubmittedReportsReturned(): void
    {
        $activeReport = new Report();
        $submittedReport1 = new Report()->setSubmitted(true);
        $unsubmittedReport = new Report()->setSubmitted(false)->setUnSubmitDate(new \DateTime());
        $submittedReport2 = new Report()->setSubmitted(true);

        $this->courtOrder->setReports([
            $activeReport,
            $submittedReport1,
            $unsubmittedReport,
            $submittedReport2,
        ]);

        $result = $this->courtOrder->getSubmittedReports();

        self::assertEquals(
            [
                $submittedReport1,
                $submittedReport2,
            ],
            $result
        );
    }

    public static function hasCoDeputiesProvider(): array
    {
        return [
            'No deputies' => [[], false],
            'Only one deputy' => [[new Deputy()], false],
            'Multiple deputies' => [[new Deputy(), new Deputy()], true],
        ];
    }

    /**
     *
     * @param array<Deputy> $activeDeputies
     */
    #[DataProvider('hasCoDeputiesProvider')]
    public function testHasCoDeputiesNoDeputies(array $activeDeputies, bool $expectedResult): void
    {
        $this->courtOrder->setActiveDeputies($activeDeputies);

        self::assertEquals($expectedResult, $this->courtOrder->hasCoDeputies());
    }

    public function testGetCoDeputiesWithNoDeputies(): void
    {
        self::assertEquals([], $this->courtOrder->getCoDeputies());
    }

    public function testGetCoDeputiesExcludesLoggedInDeputyAndIsOrderedByFirstname()
    {
        $loggedInDeputy = new Deputy()->setFirstname('Bill')->setDeputyUid('1234');
        $coDeputy1 = new Deputy()->setFirstname('Zoé')->setDeputyUid('5678');
        $coDeputy2 = new Deputy()->setFirstname('Floyd')->setDeputyUid('4321');

        $this->courtOrder->setActiveDeputies(
            [
                $loggedInDeputy,
                $coDeputy1,
                $coDeputy2,
            ]
        );

        self::assertEquals(
            [
                $loggedInDeputy,
                $coDeputy2,
                $coDeputy1,
            ],
            $this->courtOrder->getCoDeputies()
        );
    }

    public function testGetHybridCourtOrderType()
    {
        $hybridReport = new Report()->setType('103-4');

        // both hybrid court orders are assigned a hw order type
        $pfaCourtOrder = new CourtOrder()->setOrderType('hw');
        $hwCourtOrder = new CourtOrder()->setOrderType('hw');

        $pfaCourtOrder->setReports([$hybridReport]);
        $hwCourtOrder->setReports([$hybridReport]);

        $courtOrderTypePfa = $pfaCourtOrder->getActiveReportType();
        $courtOrderTypeHw = $hwCourtOrder->getActiveReportType();

        self::assertEquals('Property & Affairs with Health & Welfare Report', $courtOrderTypeHw);
        self::assertEquals('Property & Affairs with Health & Welfare Report', $courtOrderTypePfa);
    }
}

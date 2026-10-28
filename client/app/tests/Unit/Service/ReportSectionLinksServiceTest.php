<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Frontend\Unit\Service;

use OPG\Digideps\Frontend\Entity\Report\Report;
use OPG\Digideps\Frontend\Service\ReportSectionsLinkService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

class ReportSectionLinksServiceTest extends TestCase
{
    protected ReportSectionsLinkService $sut;
    private Report&MockObject $report;

    public function setUp(): void
    {
        $router = self::createMock(RouterInterface::class);
        $router->method('generate')->willReturnCallback(function ($a, $b) {
            return $a . http_build_query($b);
        });
        $this->report = self::createMock(Report::class);

        $this->report->method('getId')->willReturn(1);

        $this->sut = new ReportSectionsLinkService($router);
    }

    public function testGetSectionParamsLay(): void
    {
        $this->report->method('hasSection')->willReturnMap([
            ['decisions', true],
            ['contacts', true],
            ['visitsCare', true],
            ['lifestyle', true],
            ['clientBenefitsCheck', true],
            ['bankAccounts', true],
            ['deputyExpenses', true],
            ['gifts', true],
            ['moneyTransfers', true],
            ['moneyIn', true],
            ['moneyOut', true],
            ['moneyInShort', true],
            ['moneyOutShort', true],
            ['assets', true],
            ['debts', true],
            ['paDeputyExpenses', true],
            ['actions', true],
            ['otherInfo', true],
            ['documents', true],
            ['profCurrentFees', false],
            ['deputyExpenses', false],
            ['profDeputyCosts', false],
            ['profDeputyCostsEstimate', false],
            ['paDeputyExpenses', false],
        ]);

        $actual = $this->sut->getSectionParams($this->report, 'deputyExpenses', 1);
        self::assertEquals('gifts', $actual['section']);

        $actual = $this->sut->getSectionParams($this->report, 'documents', +1);
        self::assertEquals([], $actual);
    }

    public function testGetSectionParamsPa(): void
    {
        $this->report->method('isPAreport')->willReturn(true);

        $this->report->method('hasSection')->willReturnMap([
            ['decisions', true],
            ['contacts', true],
            ['visitsCare', true],
            ['lifestyle', true],
            ['clientBenefitsCheck', true],
            ['bankAccounts', true],
            ['gifts', true],
            ['moneyTransfers', true],
            ['moneyIn', true],
            ['moneyOut', true],
            ['moneyInShort', true],
            ['moneyOutShort', true],
            ['assets', true],
            ['debts', true],
            ['paDeputyExpenses', true],
            ['actions', true],
            ['otherInfo', true],
            ['documents', true],
            ['profCurrentFees', false],
            ['deputyExpenses', false],
            ['profDeputyCosts', false],
            ['profDeputyCostsEstimate', false],
        ]);

        $actual = $this->sut->getSectionParams($this->report, 'paFeeExpense', +1);
        self::assertEquals('actions', $actual['section']);
    }

    public function testGetSectionParamsProf(): void
    {
        $this->report->method('isPAreport')->willReturn(false);
        $this->report->method('isProfReport')->willReturn(true);


        $this->report->method('hasSection')->willReturnMap([
            ['decisions', true],
            ['contacts', true],
            ['visitsCare', true],
            ['lifestyle', true],
            ['clientBenefitsCheck', true],
            ['bankAccounts', true],
            ['gifts', true],
            ['moneyTransfers', true],
            ['moneyIn', true],
            ['moneyOut', true],
            ['moneyInShort', true],
            ['moneyOutShort', true],
            ['assets', true],
            ['debts', true],
            ['profCurrentFees', true],
            ['profDeputyCosts', true],
            ['profDeputyCostsEstimate', true],
            ['actions', true],
            ['otherInfo', true],
            ['documents', true],
            ['paDeputyExpenses', false],
            ['deputyExpenses', false],
        ]);

        $actual = $this->sut->getSectionParams($this->report, 'profDeputyCosts', +1);
        self::assertEquals('profDeputyCostsEstimate', $actual['section']);
    }
}

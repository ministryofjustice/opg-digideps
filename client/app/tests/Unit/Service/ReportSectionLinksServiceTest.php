<?php

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
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->willReturnCallback(function ($a, $b) {
            return $a . http_build_query($b);
        });
        $this->report = $this->createMock(Report::class);

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
            ['actions', true],
            ['actions', true],
            ['otherInfo', true],
            ['gifts', true],
            ['clientBenefitsCheck', true],
            ['bankAccounts', true],
            ['moneyTransfers', true],
            ['moneyIn', true],
            ['moneyOut', true],
            ['moneyInShort', true],
            ['moneyOutShort', true],
            ['assets', true],
            ['debts', true],
            ['documents', true],
            ['paDeputyExpenses', false],
            ['profCurrentFees', false],
            ['actions', true],
            ['profDeputyCosts', false],
            ['deputyExpenses', true],
        ]);

        $actual = $this->sut->getSectionParams($this->report, 'debts', 1);
        $this->assertEquals('actions', $actual['section']);

        $actual = $this->sut->getSectionParams($this->report, 'documents', +1);
        $this->assertEquals([], $actual);
    }

    public function testGetSectionParamsPa(): void
    {
        $this->report->method('hasSection')->willReturnMap([
            ['decisions', true],
            ['contacts', true],
            ['visitsCare', true],
            ['lifestyle', true],
            ['actions', true],
            ['actions', true],
            ['otherInfo', true],
            ['gifts', true],
            ['clientBenefitsCheck', true],
            ['bankAccounts', true],
            ['moneyTransfers', true],
            ['moneyIn', true],
            ['moneyOut', true],
            ['moneyInShort', true],
            ['moneyOutShort', true],
            ['assets', true],
            ['debts', true],
            ['documents', true],
            ['paDeputyExpenses', true],
            ['profCurrentFees', false],
            ['deputyExpenses', false],
            ['profDeputyCosts', false],
            ['profDeputyCostsEstimate', false],
        ]);

        $actual = $this->sut->getSectionParams($this->report, 'paFeeExpense', +1);
        $this->assertEquals('gifts', $actual['section']);
    }

    public function testGetSectionParamsProf(): void
    {
        $this->report->method('hasSection')->willReturnMap([
            ['decisions', true],
            ['contacts', true],
            ['visitsCare', true],
            ['lifestyle', true],
            ['actions', true],
            ['actions', true],
            ['otherInfo', true],
            ['gifts', true],
            ['clientBenefitsCheck', true],
            ['bankAccounts', true],
            ['moneyTransfers', true],
            ['moneyIn', true],
            ['moneyOut', true],
            ['moneyInShort', true],
            ['moneyOutShort', true],
            ['assets', true],
            ['debts', true],
            ['documents', true],
            ['paDeputyExpenses', false],
            ['profCurrentFees', false],
            ['profDeputyCosts', true],
            ['deputyExpenses', false],
            ['profDeputyCostsEstimate', true],
        ]);

        $actual = $this->sut->getSectionParams($this->report, 'profDeputyCosts', +1);
        $this->assertEquals('profDeputyCostsEstimate', $actual['section']);
    }
}

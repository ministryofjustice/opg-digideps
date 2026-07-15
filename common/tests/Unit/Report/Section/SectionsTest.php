<?php

declare(strict_types=1);

namespace Tests\OPG\Digideps\Common\Unit\Report\Section;

use OPG\Digideps\Common\Report\ReportType;
use OPG\Digideps\Common\Report\Section\ReportSection;
use OPG\Digideps\Common\Report\Section\Sections;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SectionsTest extends TestCase
{
    /**
     * @return array<array{string, array<string>}>
     */
    public static function allSectionIds(): array
    {
        return [
            ['102', [
                'decisions',
                'contacts',
                'visitsCare',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyTransfers',
                'moneyIn',
                'moneyOut',
                'balance',
                'assets',
                'debts',
                'deputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['102-4', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyTransfers',
                'moneyIn',
                'moneyOut',
                'balance',
                'assets',
                'debts',
                'deputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['103', [
                'decisions',
                'contacts',
                'visitsCare',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyInShort',
                'moneyOutShort',
                'assets',
                'debts',
                'deputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['103-4', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyInShort',
                'moneyOutShort',
                'assets',
                'debts',
                'deputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['104', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'deputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['102-5', [
                'decisions',
                'contacts',
                'visitsCare',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyTransfers',
                'moneyIn',
                'moneyOut',
                'balance',
                'assets',
                'debts',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['102-4-5', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyTransfers',
                'moneyIn',
                'moneyOut',
                'balance',
                'assets',
                'debts',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['103-5', [
                'decisions',
                'contacts',
                'visitsCare',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyInShort',
                'moneyOutShort',
                'assets',
                'debts',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['103-4-5', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyInShort',
                'moneyOutShort',
                'assets',
                'debts',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['104-5', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'profDeputyCosts',
                'profDeputyCostsEstimate',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['102-6', [
                'decisions',
                'contacts',
                'visitsCare',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyTransfers',
                'moneyIn',
                'moneyOut',
                'balance',
                'assets',
                'debts',
                'paDeputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['102-4-6', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyTransfers',
                'moneyIn',
                'moneyOut',
                'balance',
                'assets',
                'debts',
                'paDeputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['103-6', [
                'decisions',
                'contacts',
                'visitsCare',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyInShort',
                'moneyOutShort',
                'assets',
                'debts',
                'paDeputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['103-4-6', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'clientBenefitsCheck',
                'bankAccounts',
                'gifts',
                'moneyInShort',
                'moneyOutShort',
                'assets',
                'debts',
                'paDeputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
            ['104-6', [
                'decisions',
                'contacts',
                'visitsCare',
                'lifestyle',
                'paDeputyExpenses',
                'actions',
                'otherInfo',
                'documents',
            ]],
        ];
    }

    public function testGetSectionBefore(): void
    {
        $sections = Sections::new(ReportType::from('104-5'));
        $this->assertSame(null, $sections->getSectionBefore(ReportSection::DECISIONS));
        $this->assertSame(ReportSection::VISITS_CARE, $sections->getSectionBefore(ReportSection::LIFESTYLE));
        $this->assertSame(null, $sections->getSectionBefore(ReportSection::DEPUTY_EXPENSES));
        $this->assertSame(ReportSection::LIFESTYLE, $sections->getSectionBefore(ReportSection::PROF_DEPUTY_COSTS));
        $this->assertSame(null, $sections->getSectionBefore(ReportSection::MONEY_OUT));
        $this->assertSame(ReportSection::OTHER_INFO, $sections->getSectionBefore(ReportSection::DOCUMENTS));
    }

    public function testHasSection(): void
    {
        $sections = Sections::new(ReportType::from('104-5'));
        $this->assertTrue($sections->hasSection(ReportSection::DECISIONS));
        $this->assertTrue($sections->hasSection(ReportSection::LIFESTYLE));
        $this->assertFalse($sections->hasSection(ReportSection::DEPUTY_EXPENSES));
        $this->assertTrue($sections->hasSection(ReportSection::PROF_DEPUTY_COSTS));
        $this->assertFalse($sections->hasSection(ReportSection::MONEY_OUT));
        $this->assertTrue($sections->hasSection(ReportSection::DOCUMENTS));
    }

    /**
     * @param array<string> $sectionIds
     */
    #[DataProvider('allSectionIds')]
    public function testAsSectionIdArray(string $reportType, array $sectionIds): void
    {
        $sections = Sections::new(ReportType::from($reportType));
        $this->assertSame($sectionIds, $sections->asSectionIdArray());
    }

    /**
     * @param array<string> $sectionIds
     */
    #[DataProvider('allSectionIds')]
    public function testGetIterator(string $reportType, array $sectionIds): void
    {
        $sections = Sections::new(ReportType::from($reportType));
        $this->assertSame(array_map(fn (string $sectionId) => ReportSection::from($sectionId), $sectionIds), [...$sections->getIterator()]);
    }

    public function testGetSectionAfter(): void
    {
        $sections = Sections::new(ReportType::from('104-5'));
        $this->assertSame(ReportSection::CONTACTS, $sections->getSectionAfter(ReportSection::DECISIONS));
        $this->assertSame(ReportSection::PROF_DEPUTY_COSTS, $sections->getSectionAfter(ReportSection::LIFESTYLE));
        $this->assertSame(null, $sections->getSectionAfter(ReportSection::DEPUTY_EXPENSES));
        $this->assertSame(ReportSection::PROF_DEPUTY_COSTS_ESTIMATE, $sections->getSectionAfter(ReportSection::PROF_DEPUTY_COSTS));
        $this->assertSame(null, $sections->getSectionAfter(ReportSection::MONEY_OUT));
        $this->assertSame(null, $sections->getSectionAfter(ReportSection::DOCUMENTS));
    }
}

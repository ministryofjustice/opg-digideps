<?php

namespace App\Entity\Ndr;

use App\Entity\Client;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class NdrTest extends TestCase
{
    /** @var Ndr */
    private $ndr;

    protected function setUp(): void
    {
        $this->ndr = new Ndr();
        $this->incomeTicked = new StateBenefit('t1', true);
        $this->incomeUnticked = new StateBenefit('t2', false);

        $this->client = $this->createMock(Client::class);
        $this->ndr->setClient($this->client);
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testgetStateBenefitOther()
    {
        $ndr = new Ndr();

        $ndr->setStateBenefits([]);
        $this->assertNull($ndr->getStateBenefitOther());

        $ndr->setStateBenefits([new StateBenefit('other_benefits', true)]);
        $this->assertEquals('other_benefits', $ndr->getStateBenefitOther()->getTypeId());
    }

    public function testIncomeBenefitsStatus()
    {
        $this->assertEquals('not-started', $this->ndr->incomeBenefitsStatus());

        $this->ndr->setStateBenefits([$this->incomeTicked]);
        $this->assertEquals('incomplete', $this->ndr->incomeBenefitsStatus(), 'state benefits and one-off should be ignored');

        $this->ndr->setReceiveStatePension('yes');
        $this->ndr->setReceiveOtherIncome('yes');
        $this->ndr->setReceiveOtherIncomeDetails('..');
        $this->ndr->setExpectCompensationDamages('yes');
        $this->ndr->setExpectCompensationDamagesDetails('..');

        // state benefits and one-off should be ignored
        $this->ndr->setStateBenefits([$this->incomeUnticked]);
        $this->ndr->setOneOff([$this->incomeUnticked]);

        $this->assertEquals('done', $this->ndr->incomeBenefitsStatus());
    }

    public function testGetAssetsTotalValue()
    {
        $this->ndr->setAssets([
            m::mock(AssetOther::class, ['getValueTotal' => 1]),
            m::mock(AssetProperty::class, ['getValueTotal' => 2]),
        ]);

        $this->assertEquals(3, $this->ndr->getAssetsTotalValue());
    }

    public function testValidForSubmissionAllFieldsPresent()
    {
        $this->client->method('getCourtDate')->willReturn('2024-01-01');
        $this->client->method('getCaseNumber')->willReturn('123456');
        $this->client->method('getAddress')->willReturn('123 Main St');
        $this->client->method('getPostcode')->willReturn('AB12 3CD');

        $result = $this->ndr->validForSubmission();

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['msg']);
    }

    public function testValidForSubmissionMissingCourtDate()
    {
        $this->client->method('getCourtDate')->willReturn(null);
        $this->client->method('getCaseNumber')->willReturn('123456');
        $this->client->method('getAddress')->willReturn('123 Main St');
        $this->client->method('getPostcode')->willReturn('AB12 3CD');

        $result = $this->ndr->validForSubmission();

        $this->assertFalse($result['valid']);
        $this->assertContains('Missing Court Date on Client', $result['msg']);
    }

    public function testValidForSubmissionMissingCaseNumber()
    {
        $this->client->method('getCourtDate')->willReturn('2024-01-01');
        $this->client->method('getCaseNumber')->willReturn(null);
        $this->client->method('getAddress')->willReturn('123 Main St');
        $this->client->method('getPostcode')->willReturn('AB12 3CD');

        $result = $this->ndr->validForSubmission();

        $this->assertFalse($result['valid']);
        $this->assertContains('Missing CaseNumber on Client', $result['msg']);
    }

    public function testValidForSubmissionMissingOptionalFields()
    {
        $this->client->method('getCourtDate')->willReturn('2024-01-01');
        $this->client->method('getCaseNumber')->willReturn('123456');
        $this->client->method('getAddress')->willReturn(null);
        $this->client->method('getPostcode')->willReturn(null);

        $result = $this->ndr->validForSubmission();

        $this->assertTrue($result['valid']);
        $this->assertContains('Missing Address on Client', $result['msg']);
        $this->assertContains('Missing Postcode on Client', $result['msg']);
    }
}

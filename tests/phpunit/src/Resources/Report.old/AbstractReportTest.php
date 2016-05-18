<?php

namespace AppBundle\Resources\views\Report;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Report as Report;
use Symfony\Component\HttpFoundation\Request;
use Mockery as m;

class AbstractReportTest extends WebTestCase
{
    /**
     * @var Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    protected $report;
    protected $reportClient;
    protected $deputy;
    protected $decisions;
    protected $contacts;

    protected $twig;

    public function setUp()
    {
        $this->frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false]);
        $this->frameworkBundleClient->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->frameworkBundleClient->getContainer()->set('request', $request, 'request');
        $this->twig = $this->frameworkBundleClient->getContainer()->get('templating');
    }

    public function tearDown()
    {
        m::close();
    }

    protected function setupDecisions()
    {
        $decision1 = m::mock('AppBundle\Entity\Decision')
            ->shouldReceive('getDescription')->andReturn('3 beds')
            ->shouldReceive('getClientInvolved')->andReturn(true)
            ->shouldReceive('getClientInvolvedDetails')->andReturn('the client was able to decide at 85%')
            ->getMock();

        $decision2 = m::mock('AppBundle\Entity\Decision')
            ->shouldReceive('getDescription')->andReturn('2 televisions')
            ->shouldReceive('getClientInvolved')->andReturn(false)
            ->shouldReceive('getClientInvolvedDetails')->andReturn('the client said he doesnt want a tv anymore')
            ->getMock();

        $this->decisions = [$decision1, $decision2];
    }

    protected function setupContacts()
    {
        $contact1 = m::mock('AppBundle\Entity\Contact')
            ->shouldReceive('getContactName')->andReturn('Any White')
            ->shouldReceive('getRelationship')->andReturn('brother')
            ->shouldReceive('getExplanation')->andReturn('no explanation')
            ->shouldReceive('getAddress')->andReturn('45 Noth Road')
            ->shouldReceive('getAddress2')->andReturn('Islington')
            ->shouldReceive('getCounty')->andReturn('London')
            ->shouldReceive('getPostcode')->andReturn('N2 5JF')
            ->shouldReceive('getCountry')->andReturn('GB')
            ->getMock();

        $contact2 = m::mock('AppBundle\Entity\Contact')
            ->shouldReceive('getContactName')->andReturn('Fred Smith')
            ->shouldReceive('getRelationship')->andReturn('Social Worker')
            ->shouldReceive('getExplanation')->andReturn('Advices on benefits and stuff')
            ->shouldReceive('getAddress')->andReturn('Town Hall')
            ->shouldReceive('getAddress2')->andReturn('Maidenhead')
            ->shouldReceive('getCounty')->andReturn('Berkshire')
            ->shouldReceive('getPostcode')->andReturn('SL1 1YY')
            ->shouldReceive('getCountry')->andReturn('GB')
            ->getMock();

        $this->contacts = [$contact1, $contact2];
    }

    protected function setupReport()
    {
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $this->report = m::mock('AppBundle\Entity\Report')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getSubmitted')->andReturn(false)
            ->shouldReceive('getId')->andReturn(1)
            ->shouldReceive('getCourtOrderType')->andReturn(Report::PROPERTY_AND_AFFAIRS)
            ->shouldReceive('getStartDate')->andReturn($startDate)
            ->shouldReceive('getEndDate')->andReturn($endDate)
            ->shouldReceive('getDecisions')->andReturn($this->decisions)
            ->getMock();
    }

    protected function setupReportClient()
    {
        $this->reportClient = m::mock('AppBundle\Entity\Client')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getCaseNumber')->andReturn('12341234')
            ->shouldReceive('getFirstname')->andReturn('Leroy')
            ->shouldReceive('getLastname')->andReturn('Cross-Tolley')
            ->shouldReceive('getAddress')->andReturn('Blackthorn Cottage')
            ->shouldReceive('getAddress2')->andReturn('Chawridge Lane')
            ->shouldReceive('getCounty')->andReturn('Berkshire')
            ->shouldReceive('getPostcode')->andReturn('SL4 4QR')
            ->shouldReceive('getPhone')->andReturn('07814 013561')
            ->getMock();
    }

    protected function setupDeputy()
    {
        $this->deputy = m::mock('AppBundle\Entity\User')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getFirstname')->andReturn('Zac')
            ->shouldReceive('getLastname')->andReturn('Tolley')
            ->shouldReceive('getAddress1')->andReturn('Blackthorn Cottage')
            ->shouldReceive('getAddress2')->andReturn('Chawridge Lane')
            ->shouldReceive('getAddress3')->andReturn('Berkshire')
            ->shouldReceive('getAddressPostcode')->andReturn('SL4 4QR')
            ->shouldReceive('getPhoneMain')->andReturn('07814 013561')
            ->shouldReceive('getEmail')->andReturn('zac@thetolleys.com')
            ->getMock();
    }

    public function testEmpty()
    {
        $this->assertEquals(1, 1);
        // Dumb test to get rid of warning until I figure why changing the name didn't work.
    }

    protected function getAccountMock()
    {
        $startDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2014');
        $endDate = \DateTime::createFromFormat('j-M-Y', '1-Jan-2015');

        $moneyIn = $this->getMoneyIn();
        $moneyOut = $this->getMoneyOut();

        $account1 = m::mock('AppBundle\Entity\Account')
            ->shouldIgnoreMissing(true)
            ->shouldReceive('getBank')->andReturn('HSBC')
            ->shouldReceive('getSortCode')->andReturn('444444')
            ->shouldReceive('getAccountNumber')->andReturn('7999')
            ->shouldReceive('getOpeningDate')->andReturn($startDate)
            ->shouldReceive('getOpeningBalance')->andReturn(100.00)
            ->shouldReceive('getClosingBalance')->andReturn(100.00)
            ->shouldReceive('getClosingDate')->andReturn($endDate)
            ->shouldReceive('getMoneyInTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyOutTotal')->andReturn(10000.00)
            ->shouldReceive('getMoneyTotal')->andReturn(0.00)
            ->shouldReceive('getMoneyIn')->andReturn($moneyIn)
            ->shouldReceive('getMoneyOut')->andReturn($moneyOut)
            ->shouldReceive('getOpeningDateExplanation')->andReturn(null)
            ->shouldReceive('getClosingDateExplanation')->andReturn(null)
            ->shouldReceive('getClosingBalanceExplanation')->andReturn(null)
            ->getMock();

        return $account1;
    }

    protected function getMoneyIn()
    {
        return [
            ['type' => 'disability_living_allowance_or_personal_independence_payment', 'amount' => 1.00],
            ['type' => 'attendance_allowance', 'amount' => 10000.01],
            ['type' => 'employment_support_allowance_or_incapacity_benefit', 'amount' => 10000.01],
            ['type' => 'severe_disablement_allowance', 'amount' => 10000.01],
            ['type' => 'income_support_or_pension_credit', 'amount' => 10000.01],
            ['type' => 'housing_benefit', 'amount' => 10000.01],
            ['type' => 'state_pension', 'amount' => 10000.01],
            ['type' => 'universal_credit', 'amount' => 10000.01],
            ['type' => 'other_benefits_eg_winter_fuel_or_cold_weather_payments', 'amount' => 10000.01],
            ['type' => 'occupational_pensions', 'amount' => 10000.01],
            ['type' => 'account_interest', 'amount' => 10000.01],
            ['type' => 'income_from_investments_property_or_dividends', 'amount' => 10000.01],
            ['type' => 'salary_or_wages', 'amount' => 10000.01],
            ['type' => 'refunds', 'amount' => 10000.01],
            ['type' => 'bequests_eg_inheritance_gifts_received', 'amount' => 10000.01],
            ['type' => 'sale_of_investments_property_or_assets', 'amount' => 10000.01],
            ['type' => 'further_guidance', 'amount' => 10000.01, 'moreDetails' => 'more 1'],
            ['type' => 'compensation_or_damages_awards', 'amount' => 10000.01, 'moreDetails' => 'more 2'],
            ['type' => 'transfers_in_from_client_s_other_accounts', 'amount' => 10000.01, 'moreDetails' => 'more 3'],
            ['type' => 'any_other_money_paid_in_and_not_listed_above', 'amount' => 10000.01, 'moreDetails' => 'more 4'],
        ];
    }

    protected function getMoneyOut()
    {
        return [
            ['type' => 'care_fees_or_local_authority_charges_for_care', 'amount' => 10000.01],
            ['type' => 'accommodation_costs_eg_rent_mortgage_service_charges', 'amount' => 10000.01],
            ['type' => 'household_bills_eg_water_gas_electricity_phone_council_tax', 'amount' => 10000.01],
            ['type' => 'day_to_day_living_costs_eg_food_toiletries_clothing_sundries', 'amount' => 10000.01],
            ['type' => 'debt_payments_eg_loans_cards_care_fee_arrears', 'amount' => 10000.01],
            ['type' => 'travel_costs_for_client_eg_bus_train_taxi_fares', 'amount' => 10000.01],
            ['type' => 'holidays_or_day_trips', 'amount' => 10000.01],
            ['type' => 'tax_payable_to_hmrc', 'amount' => 10000.01],
            ['type' => 'insurance_eg_life_home_and_contents', 'amount' => 10000.01],
            ['type' => 'office_of_the_public_guardian_fees', 'amount' => 10000.01],
            ['type' => 'deputy_s_security_bond', 'amount' => 10000.01],
            ['type' => 'client_s_personal_allowance_eg_spending_money', 'amount' => 10000.01, 'moreDetails' => 'more 1'],
            ['type' => 'cash_withdrawals', 'amount' => 10000.01, 'moreDetails' => 'more 2'],
            ['type' => 'professional_fees_eg_solicitor_or_accountant_fees', 'amount' => 10000.01, 'moreDetails' => 'more 3'],
            ['type' => 'deputy_s_expenses', 'amount' => 10000.01, 'moreDetails' => 'more 4'],
            ['type' => 'gifts', 'amount' => 10000.01, 'moreDetails' => 'more 5'],
            ['type' => 'major_purchases_eg_property_vehicles', 'amount' => 10000.01, 'moreDetails' => 'more 6'],
            ['type' => 'property_maintenance_or_improvement', 'amount' => 10000.01, 'moreDetails' => 'more 7'],
            ['type' => 'investments_eg_shares_bonds_savings', 'amount' => 10000.01, 'moreDetails' => 'more 8'],
            ['type' => 'transfers_out_to_other_client_s_accounts', 'amount' => 10000.01, 'moreDetails' => 'more 9'],
            ['type' => 'any_other_money_paid_out_and_not_listed_above', 'amount' => 10000.01, 'moreDetails' => 'more 10'],
        ];
    }

    protected function setupAccounts()
    {
        $account = $this->getAccountMock();
        $this->report->shouldReceive('getAccounts')->andReturn([$account]);
    }

    // Find a checkbox in the given container (css) in a form with a given name and a 
    // look for a checkbox with the label give and check it's state matches
    // e.g. assertCheckboxChecked($crawler, '#safeguarding-section','Do you live with the client?', 'yes')
    protected function assertCheckboxChecked($crawler, $container, $checkBoxLegend, $checkedValue = null)
    {
        $containerElement = $crawler->filter($container)->eq(0);
        $this->assertEquals(1, $containerElement->count());
        $checkBoxLegend = preg_replace('/[^A-Za-z0-9 ]/', '', $checkBoxLegend);

        if ($checkedValue == null) {
            $css = '[data-checkbox="'.$this->replace_dashes($checkBoxLegend).'"]';
        } else {
            $css = '[data-checkbox="'.$this->replace_dashes($checkBoxLegend).'--'.$this->replace_dashes($checkedValue).'"]';
        }

        $element = $containerElement->filter($css);
        $this->assertEquals(1, $element->count());
        $this->assertContains('X', $element->eq(0)->text());
    }

    protected function assertCheckboxNotChecked($crawler, $container, $checkBoxLegend, $checkedValue = null)
    {
        $containerElement = $crawler->filter($container)->eq(0);
        $this->assertEquals(1, $containerElement->count());
        $checkBoxLegend = preg_replace('/[^A-Za-z0-9 ]/', '', $checkBoxLegend);

        if ($checkedValue == null) {
            $css = '[data-checkbox="'.$this->replace_dashes($checkBoxLegend).'"]';
        } else {
            $css = '[data-checkbox="'.$this->replace_dashes($checkBoxLegend).'--'.$this->replace_dashes($checkedValue).'"]';
        }

        $element = $containerElement->filter($css);
        $this->assertEquals(1, $element->count());
        $this->assertNotContains('X', $element->eq(0)->text());
    }

    private function replace_dashes($string)
    {
        $string = str_replace(' ', '-', $string);

        return strtolower($string);
    }

    protected function assertSectionDoesntExist($crawler, $section)
    {
        $elements = $crawler->filter($section);
        $this->assertEquals(0, $elements->count());
    }

    protected function assertSectionDoesExist($crawler, $section)
    {
        $elements = $crawler->filter($section);
        $this->assertEquals(1, $elements->count());
    }

    protected function assertSectionContainsText($crawler, $section, $text)
    {
        $this->assertContains($text, $crawler->filter($section)->eq(0)->text());
    }
}

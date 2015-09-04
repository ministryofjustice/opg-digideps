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
        $this->client = static::createClient([ 'environment' => 'test','debug' => false ]);
        $this->client->getContainer()->enterScope('request');
        $request = new Request();
        $request->create('/');
        $this->client->getContainer()->set('request', $request, 'request');
        $this->twig = $this->client->getContainer()->get('templating');
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
            ->shouldReceive('getClientInvolvedDetails')->andReturn("the client was able to decide at 85%")
            ->getMock();

        $decision2 = m::mock('AppBundle\Entity\Decision')
            ->shouldReceive('getDescription')->andReturn('2 televisions')
            ->shouldReceive('getClientInvolved')->andReturn(false)
            ->shouldReceive('getClientInvolvedDetails')->andReturn("the client said he doesnt want a tv anymore")
            ->getMock();

        $this->decisions = [$decision1, $decision2];

    }

    protected function setupContacts() {
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


}
<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\RequestIdLoggerProcessor;
use Mockery as m;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestIdLoggerProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestIdLoggerProcessor
     */
    private $object;

    private $record = ['key1' => 'abc', 'key2' => 2];

    public function setUp()
    {
        $this->container = m::mock('Symfony\Component\DependencyInjection\Container');
        $this->reqStack = m::mock('Symfony\Component\HttpFoundation\RequestStack');

        $this->object = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordHasNoReqStack()
    {
        $this->container->shouldReceive('has')->with('request_stack')->andReturn(false);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);

        $this->container
            ->shouldReceive('has')->with('request_stack')->andReturn(true)
            ->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-request-id', 'THIS_IS_THE_REQUEST_ID');

        $this->reqStack->shouldReceive('getCurrentRequest')->andReturn($request);

        $this->container
            ->shouldReceive('has')->with('request_stack')->andReturn(true)
            ->shouldReceive('get')->with('request_stack')->andReturn($this->reqStack);

        $this->assertEquals($this->record + ['extra' => ['request_id' => 'THIS_IS_THE_REQUEST_ID']], $this->object->processRecord($this->record));
    }

    public function tearDown()
    {
        m::close();
    }
}

<?php

namespace AppBundle\Service;

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

        $this->object = new RequestIdLoggerProcessor($this->container);
    }

    public function testProcessRecordScopeInactive()
    {
        $this->container->shouldReceive('isScopeActive')->with('request')->andReturn(false);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequest()
    {
        $this->container
            ->shouldReceive('isScopeActive')->with('request')->andReturn(true)
            ->shouldReceive('has')->with('request')->andReturn(false);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasNoRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();

        $this->container
            ->shouldReceive('isScopeActive')->with('request')->andReturn(true)
            ->shouldReceive('has')->with('request')->andReturn(true)
            ->shouldReceive('get')->with('request')->andReturn($request);

        $this->assertEquals($this->record, $this->object->processRecord($this->record));
    }

    public function testProcessRecordHasRequestId()
    {
        $request = new Request();
        $request->headers = new ParameterBag();
        $request->headers->set('x-request-id', 'THIS_IS_THE_REQUEST_ID');

        $this->container
            ->shouldReceive('isScopeActive')->with('request')->andReturn(true)
            ->shouldReceive('has')->with('request')->andReturn(true)
            ->shouldReceive('get')->with('request')->andReturn($request);

        $this->assertEquals($this->record + ['extra' => ['request_id' => 'THIS_IS_THE_REQUEST_ID']], $this->object->processRecord($this->record));
    }

    public function tearDown()
    {
        m::close();
    }
}

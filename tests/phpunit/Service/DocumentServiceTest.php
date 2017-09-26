<?php

namespace AppBundle\Service;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\Report\ReportSubmission;
use AppBundle\Entity\User;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\File\Storage\S3Storage;
use Mockery\Exception;
use MockeryStub as m;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class DocumentServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DocumentService
     */
    protected $object;

    /**
     * @var S3Storage
     */
    private $s3Storage;

    /**
     * @var RestClient
     */
    private $restClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->s3Storage = m::mock(S3Storage::class);
        $this->restClient = m::mock(RestClient::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();

        $this->object = new DocumentService($this->s3Storage, $this->restClient, $this->logger);
    }


    public function testremoveOld()
    {
        $this->s3Storage
            ->shouldReceive('delete')->once()->with('r1')
            ->shouldReceive('delete')->once()->with('r2')
            ->shouldReceive('delete')->once()->with('r3');

        $this->restClient
            // 2 submissions, 3 docs in total
            ->shouldReceive('apiCall')->once()->with('GET', 'report-submission/old', null, 'Report\ReportSubmission[]', [], false)->andReturn([
                m::mock(ReportSubmission::class, ['getId'=>1, 'getDocuments'=>[
                    m::mock(Document::class, ['getId'=>1, 'getStorageReference'=>'r1']),
                    m::mock(Document::class, ['getId'=>1, 'getStorageReference'=>'r2']),
                ]]),
                m::mock(ReportSubmission::class, ['getId'=>2, 'getDocuments'=>[
                    m::mock(Document::class, ['getId'=>1, 'getStorageReference'=>'r3']),
                ]])
            ])
            ->shouldReceive('apiCall')->once()->with('PUT', 'report-submission/1/set-undownloadable', null, 'array', [], false)
            ->shouldReceive('apiCall')->once()->with('PUT', 'report-submission/2/set-undownloadable', null, 'array', [], false)
        ;

        $this->object->removeOld(false);
    }


    public function testremoveSoftDeleted()
    {
        $this->restClient->shouldReceive('apiCall')->once()->with('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false)->andReturn([
            m::mock(Document::class, ['getId'=>1, 'getStorageReference'=>'r1']),
            m::mock(Document::class, ['getId'=>2, 'getStorageReference'=>'r2'])
        ]);

        $this->s3Storage
            ->shouldReceive('delete')->once()->with('r1')
            ->shouldReceive('delete')->once()->with('r2');

        $this->restClient
            ->shouldReceive('apiCall')->with('DELETE', 'document/hard-delete/1', null, 'array', [], false)->once()
            ->shouldReceive('apiCall')->with('DELETE', 'document/hard-delete/2', null, 'array', [], false)->once();

        $this->object->removeSoftDeleted(false);
    }

    public static function cleanUpDataProvider()
    {
        return [
            [false, 0], // s3 failures NOT ignored -> hard delete gets called
            [true, 1], // s3 failures ignored -> hard delete gets called
        ];
    }

    /**
     * @dataProvider cleanUpDataProvider
     */
    public function testremoveSoftDeletedS3FailureFirstFailNotIgnored($ignoreS3Failures, $r1HardDeletedCalledTimes)
    {
        $this->restClient->shouldReceive('apiCall')->once()->with('GET', '/document/soft-deleted', null, 'Report\Document[]', [], false)->andReturn([
            m::mock(Document::class, ['getId'=>1, 'getStorageReference'=>'r1']),
            m::mock(Document::class, ['getId'=>2, 'getStorageReference'=>'r2'])
        ]);

        $this->s3Storage
            ->shouldReceive('delete')->once()->with('r1')->andThrow(Exception::class)
            ->shouldReceive('delete')->once()->with('r2');

        $this->restClient
            ->shouldReceive('apiCall')->with('DELETE', 'document/hard-delete/1', null, 'array', [], false)->times($r1HardDeletedCalledTimes)
            ->shouldReceive('apiCall')->with('DELETE', 'document/hard-delete/2', null, 'array', [], false)->times(1);

        $this->object->removeSoftDeleted($ignoreS3Failures);
    }


    public function tearDown()
    {
        m::close();
    }
}

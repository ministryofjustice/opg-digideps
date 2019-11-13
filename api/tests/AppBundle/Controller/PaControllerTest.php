<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Service\CsvUploader;
use AppBundle\Service\OrgService;
use Mockery as m;

class PaControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $deputy2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
        self::$deputy2 = self::fixtures()->createUser();

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp(): void
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }
    }

    public function testAddBulkAuth()
    {
        $url = '/org/bulk-add';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    public function testAddBulk()
    {
        $data = CsvUploader::compressData(array_fill(0, 30, 'example row'));

        $mockOrgService = m::mock(OrgService::class);
        /** @var \Mockery\ExpectationInterface $expectation */
        $expectation = $mockOrgService->shouldReceive('addFromCasrecRows');
        $expectation->andReturn([
            'added'    => ['prof_users' => [], 'pa_users' => ['test@gmail.com'], 'clients' => ['12345678', '23456789'], 'reports' => ['12345678-2017-03-04']],
            'errors'   => ['Error generating row 10'],
            'warnings' => ['Invalid email in row 21'],
        ]);

        $client = self::createClient([
            'environment' => 'test',
            'debug'       => false,
        ]);

        /** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */
        $container = $client->getContainer();
        $container->set('org_service', $mockOrgService);

        ob_start();
        $client->request(
            'POST',
            '/org/bulk-add',
            [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AuthToken' => self::$tokenAdmin,
            ],
            json_encode($data) ?: null
        );

        $response = ob_get_contents();
        ob_end_clean();

        if (!$response) {
            throw new \RuntimeException('Stream didn\'t return any content');
        }

        $this->assertStringContainsString('END', $response);
        $this->assertStringContainsString('ERR Error generating row 10', $response);
        $this->assertStringContainsString('WARN Invalid email in row 21', $response);
        $this->assertStringContainsString('ADD 2 CLIENTS', $response);
        $this->assertStringContainsString('ADD 1 REPORTS', $response);
    }
}

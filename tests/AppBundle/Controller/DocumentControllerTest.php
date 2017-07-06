<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bridge\Doctrine\Tests\Fixtures\User;
use Tests\AppBundle\Controller\AbstractTestController;

class DocumentControllerTest extends AbstractTestController
{
    // users
    private static $tokenDeputy;

    // lay
    private static $deputy1;
    private static $client1;

    private static $report1;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        //deputy1
        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$client1 = self::fixtures()->createClient(self::$deputy1, ['setFirstname' => 'c1']);

        // report 1
        self::$report1 = self::fixtures()->createReport(self::$client1);

        self::fixtures()->flush()->clear();
    }

    /**
     * clear fixtures.
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setup()
    {
        self::$tokenDeputy = $this->loginAsDeputy();
    }

    public function testAddDocumentForDeputy()
    {
        $reportId = self::$report1->getId();
        $url = '/report/' . $reportId . '/document';

        // assert Auth
        $this->assertEndpointNeedsAuth('POST', $url);

        // assert POST for deputy
        $data = $this->assertJsonRequest('POST', $url, [
            'mustSucceed' => true,
            'AuthToken'   => self::$tokenDeputy,
            'data'        => [
                'file_name'=> 'testfile.pdf',
                'storage_reference'   => 's3StorageKey'
            ],
        ])['data'];
    }
}

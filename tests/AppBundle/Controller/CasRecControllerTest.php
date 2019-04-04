<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\CasRec;

class CasRecControllerTest extends AbstractTestController
{
    private static $deputy1;
    private static $admin1;
    private static $deputy2;
    private static $tokenAdmin = null;
    private static $tokenDeputy = null;

    public static function setUpBeforeClass()
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
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    public function setUp()
    {
        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
        }

        $this->c1 = new CasRec([
            'Case' => '12345678',
            'Surname' => 'jones',
            'Deputy No' => 'd1',
            'Dep Surname' => 'white',
            'Dep Postcode' => 'SW1',
            'Typeofrep'=>'OPG102',
            'Corref'=>'L2',
            'custom' => 'c1',
            'custom 2' => 'c1',
        ]);
    }

    private function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    public function testTruncate()
    {
        // just to check it gets truncated
        $casRec = new CasRec([
            'Case' => 'case',
            'Surname' => 'I should get deleted',
            'Deputy No' => 'Deputy No',
            'Dep Surname' => 'Dep Surname',
            'Dep Postcode' => 'SW1',
            'Typeofrep'=>'OPG102',
            'Corref'=>'L2'
        ]);

        $this->fixtures()->persist($casRec);
        $this->fixtures()->flush($casRec);
        $this->fixtures()->clear();

        $url = '/casrec/truncate';
        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenDeputy);

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);
        $this->assertCount(0, $this->fixtures()->clear()->getRepo('CasRec')->findAll());
    }

    public function testAddBulk()
    {
        $this->fixtures()->deleteReportsData(['casrec']);

        $url = '/casrec/bulk-add';
        $this->assertEndpointNeedsAuth('POST', $url);
        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);

        // add
        $ret = $this->assertJsonRequest('POST', $url, [
            'rawData' => $this->compress([
                [
                    'Case' => '11',
                    'Surname' => 'R1',
                    'Deputy No' => 'DN1',
                    'Dep Surname' => 'R2',
                    'Dep Postcode' => 'SW1 aH3',
                    'Typeofrep' => 'OPG102',
                    'Corref' => 'L2',
                ],

            ]),
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];
        $this->assertEmpty($ret['errors'], print_r($ret, 1));
        $this->assertEquals(1, $ret['added'], print_r($ret, 1));
    }

    public function testCount()
    {
        $url = '/casrec/count';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);

        \Fixtures::deleteReportsData(['casrec']);
        $this->fixtures()->persist($this->c1)->flush($this->c1);

        // check count

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(1, $data);
    }
}

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
    }

    public function testTruncateAuth()
    {
        $url = '/casrec/truncate';

        $this->assertEndpointNeedsAuth('DELETE', $url);

        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenDeputy);
    }

    public function testAddBulkAuth()
    {
        $url = '/casrec/bulk-add';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }


    private function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    public function testTruncateAddBulkCount()
    {
        // just to check it gets truncated
        $casRec = new CasRec('case', 'I should get deleted', 'Deputy No', 'Dep Surname', 'SW1', 'OPG102', 'L2');
        $this->fixtures()->persist($casRec);
        $this->fixtures()->flush($casRec);
        $this->fixtures()->clear();

        // truncate
        $this->assertJsonRequest('DELETE', '/casrec/truncate', [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);
        $this->assertCount(0, $this->fixtures()->clear()->getRepo('CasRec')->findAll());

        // add
        $this->assertJsonRequest('POST', '/casrec/bulk-add', [
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
                [
                    'Case' => '22',
                    'Surname' => 'H1',
                    'Deputy No' => 'DN2',
                    'Dep Surname' => 'H2',
                    'Dep Postcode' => '',
                    'Typeofrep' => 'OPG103',
                    'Corref' => 'L3',
                ],

            ]),
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $records = $this->fixtures()->clear()->getRepo('CasRec')->findBy([], ['id' => 'ASC']);

        $this->assertCount(2, $records);
        $record1 = $records[0]; /* @var $record1 CasRec */
        $record2 = $records[1]; /* @var $record2 CasRec */

        $this->assertEquals('11', $record1->getCaseNumber());
        $this->assertEquals('r1', $record1->getClientLastname());
        $this->assertEquals('dn1', $record1->getDeputyNo());
        $this->assertEquals('r2', $record1->getDeputySurname());
        $this->assertEquals('sw1ah3', $record1->getDeputyPostCode());
        $this->assertEquals('OPG102', $record1->getTypeOfReport());
        $this->assertEquals('L2', $record1->getCorref());

        $this->assertEquals('22', $record2->getCaseNumber());

        // check count
        $url = '/casrec/count';

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(2, $data);
    }

    public function testCountAuth()
    {
        $url = '/casrec/count';

        $this->assertEndpointNeedsAuth('GET', $url);
    }

    public function testCountAllAcl()
    {
        $url = '/casrec/count';

        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);
    }

}

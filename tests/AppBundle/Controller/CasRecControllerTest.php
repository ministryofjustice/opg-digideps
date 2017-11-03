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
        $this->c2 = new CasRec([
            'Case' => '12345679',
            'Surname' => 'jones2',
            'Deputy No' => 'd2',
            'Dep Surname' => 'red',
            'Dep Postcode' => 'SW2',
            'Typeofrep'=>'OPG103',
            'Corref'=>'L3',
            'custom' => 'c2',
            'custom 2' => '',
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
        $this->assertJsonRequest('POST', $url, [
            'rawData' => $this->compress([
                [
                    'Case' => '11',
                    'Surname' => 'R1',
                    'Deputy No' => 'DN1',
                    'Dep Surname' => 'R2',
                    'Dep Postcode' => 'SW1 aH3',
                    'Typeofrep' => 'OPG102',
                    'Corref' => 'L2',
                    'custom1' => 'c1',
                ],
                [
                    'Case' => '22',
                    'Surname' => 'H1',
                    'Deputy No' => 'DN2',
                    'Dep Surname' => 'H2',
                    'Dep Postcode' => '',
                    'Typeofrep' => 'OPG103',
                    'Corref' => 'L3',
                    'custom 2' => 'c2',
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
        $this->assertEquals('opg102', $record1->getTypeOfReport());
        $this->assertEquals('l2', $record1->getCorref());
        $this->assertEquals('c1', $record1->getOtherColumns()['custom1']);

        $this->assertEquals('22', $record2->getCaseNumber());
        $this->assertEquals('c2', $record2->getOtherColumns()['custom 2']);

    }

    public function testGetAll()
    {
        $url = '/casrec/get-all-with-stats';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);

        $this->fixtures()->deleteReportsData(['casrec']);

        \Fixtures::deleteReportsData(['casrec']);
        $this->fixtures()->persist($this->c1, $this->c2)->flush($this->c1, $this->c2);

        // check count

        $records = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data']; /* @var $records CasRec[] */

        $this->assertCount(2, $records);
        $this->assertEquals('12345678', $records[0]['Case']);
        $this->assertEquals('c1', $records[0]['custom']);
        $this->assertEquals('c1', $records[0]['custom 2']);

        $this->assertEquals('12345679', $records[1]['Case']);
        $this->assertEquals('c2', $records[1]['custom']);
        $this->assertEquals('', $records[1]['custom 2']);
    }

    public function testCount()
    {
        $url = '/casrec/count';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);

        \Fixtures::deleteReportsData(['casrec']);
        $this->fixtures()->persist($this->c1, $this->c2)->flush($this->c1, $this->c2);

        // check count

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(2, $data);
    }

}

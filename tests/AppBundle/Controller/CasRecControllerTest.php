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


    private function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    public function testTruncate()
    {
        // just to check it gets truncated
        $casRec = new CasRec('case', 'I should get deleted', 'Deputy No', 'Dep Surname', 'SW1', 'OPG102', 'L2');
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

    public function testCount()
    {
        $url = '/casrec/count';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);

        $this->fixtures()->deleteReportsData(['casrec']);

        $c1 = new CasRec('12345678', 'jones', 'd1', 'jones', 'ha1', '102', 'corref1');
        $c2 = new CasRec('12345679', 'jones2', 'd2', 'jones2', 'ha2', '103', 'corref2');
        $this->fixtures()->persist($c1, $c2)->flush($c1, $c2);

        // check count

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(2, $data);
    }

}

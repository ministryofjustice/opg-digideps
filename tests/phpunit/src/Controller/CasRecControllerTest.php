<?php

namespace AppBundle\Controller;

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

    public function testAddBulkAuth()
    {
        $url = '/casrec/bulk-add/1';

        $this->assertEndpointNeedsAuth('POST', $url);

        $this->assertEndpointNotAllowedFor('POST', $url, self::$tokenDeputy);
    }

    private function compress($data)
    {
        return base64_encode(gzcompress(json_encode($data), 9));
    }

    public function testAddBulk()
    {
        $casRec = new CasRec('case', 'Surname', 'Deputy No', 'Dep Surname', 'SW1');
        $this->fixtures()->persist($casRec);
        $this->fixtures()->flush($casRec);

        $this->assertJsonRequest('POST', '/casrec/bulk-add/1', [
            'rawData' => $this->compress([
                [
                    'Case' => '11',
                    'Surname' => 'R1',
                    'Deputy No' => 'DN1',
                    'Dep Surname' => 'R2',
                    'Dep Postcode' => 'SW1 aH3',
                ],
                [
                    'Case' => '22',
                    'Surname' => 'H1',
                    'Deputy No' => 'DN2',
                    'Dep Surname' => 'H2',
                    'Dep Postcode' => '',
                ],

            ]),
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $users = $this->fixtures()->clear()->getRepo('CasRec')->findBy([], ['id' => 'ASC']);

        $this->assertCount(2, $users);

        $this->assertEquals('11', $users[0]->getCaseNumber());
        $this->assertEquals('r1', $users[0]->getClientLastname());
        $this->assertEquals('dn1', $users[0]->getDeputyNo());
        $this->assertEquals('r2', $users[0]->getDeputySurname());
        $this->assertEquals('sw1ah3', $users[0]->getDeputyPostCode());

        $this->assertEquals('22', $users[1]->getCaseNumber());
        $this->assertEquals('h1', $users[1]->getClientLastname());
        $this->assertEquals('dn2', $users[1]->getDeputyNo());
        $this->assertEquals('h2', $users[1]->getDeputySurname());
        $this->assertEquals('', $users[1]->getDeputyPostCode());

        // assert no-truncate
        $this->assertJsonRequest('POST', '/casrec/bulk-add/0', [
            'rawData' => $this->compress([
                [
                    'Case' => '33',
                    'Surname' => 'R3',
                    'Deputy No' => 'DN3',
                    'Dep Surname' => 'R3',
                    'Dep Postcode' => 'SW1',
                ],

            ]),
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $this->assertCount(3, $this->fixtures()->clear()->getRepo('CasRec')->findAll());
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

    /**
     * @depends testAddBulk
     */
    public function testCountAll()
    {
        $url = '/casrec/count';

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(3, $data);
    }
}

<?php

namespace App\Tests\Unit\Controller;

use App\Entity\CasRec;
use App\Tests\Unit\Fixtures;

class CasRecControllerTest extends AbstractTestController
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

        $this->c1 = new CasRec([
            'Case' => '12345678',
            'Surname' => 'jones',
            'Deputy No' => 'd1',
            'Dep Surname' => 'white',
            'Dep Postcode' => 'SW1',
            'Typeofrep' => 'OPG102',
            'Corref' => 'L2',
            'custom' => 'c1',
            'custom 2' => 'c1',
        ]);
    }

    public function testDeleteBySourceVerifiesSourceInput()
    {
        $this->buildAndPersistCasRecEntity('12345678', CasRec::CASREC_SOURCE);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $url = '/casrec/delete-by-source/unknownsource';

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenAdmin,
            'assertResponseCode' => 400,
        ]);

        $entitiesRemaining = $this->fixtures()->clear()->getRepo('CasRec')->findAll();
        $this->assertCount(1, $entitiesRemaining);
    }

    public function testDeleteBySourceDeletesBySource()
    {
        $this->buildAndPersistCasRecEntity('23410954', CasRec::CASREC_SOURCE);
        $this->buildAndPersistCasRecEntity('95043859', CasRec::SIRIUS_SOURCE);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $url = '/casrec/delete-by-source/casrec';
        $this->assertEndpointNeedsAuth('DELETE', $url);
        $this->assertEndpointNotAllowedFor('DELETE', $url, self::$tokenDeputy);

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $entitiesRemaining = $this->fixtures()->clear()->getRepo('CasRec')->findAll();
        $this->assertCount(1, $entitiesRemaining);
        $this->assertEquals('95043859', $entitiesRemaining[0]->getCaseNumber());
    }

    public function testCount()
    {
        $url = '/casrec/count';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);

        Fixtures::deleteReportsData(['casrec']);
        $this->fixtures()->persist($this->c1)->flush($this->c1);

        // check count

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(1, $data);
    }

    public function testVerifyCasRec()
    {
        $this->buildAndPersistCasRecEntity('12345678', CasRec::CASREC_SOURCE);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $this->assertJsonRequest('POST', '/casrec/verify', [
            'data' => [
                'case_number' => '12345678',
                'lastname' => 'I should get deleted',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);
    }

    /**
     * @param $case
     * @param string $source
     */
    private function buildAndPersistCasRecEntity($case, $source = CasRec::CASREC_SOURCE): CasRec
    {
        $casRec = new CasRec([
            'Case' => $case,
            'Surname' => 'I should get deleted',
            'Deputy No' => 'Deputy No',
            'Dep Surname' => 'admin',
            'Dep Postcode' => 'SW1',
            'Typeofrep' => 'OPG102',
            'Corref' => 'L2',
            'Source' => $source,
        ]);

        $this->fixtures()->persist($casRec);

        return $casRec;
    }
}

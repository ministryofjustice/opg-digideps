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
    private static $tokenProf = null;
    private static $tokenPa = null;

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
        parent::setUp();

        self::$deputy1 = self::fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        self::$admin1 = self::fixtures()->getRepo('User')->findOneByEmail('admin@example.org');
        self::$deputy2 = self::fixtures()->createUser();

        if (null === self::$tokenAdmin) {
            self::$tokenAdmin = $this->loginAsAdmin();
            self::$tokenDeputy = $this->loginAsDeputy();
            self::$tokenProf = $this->loginAsProf();
            self::$tokenPa = $this->loginAsPa();
        }

        $data = [
            'Case' => '12345678',
            'ClientSurname' => 'jones',
            'DeputyUid' => 'd1',
            'DeputySurname' => 'white',
            'DeputyAddress1' => 'Victoria Road',
            'DeputyPostcode' => 'SW1',
            'ReportType' => 'OPG102',
            'MadeDate' => '2010-03-30',
            'OrderType' => 'pfa',
        ];

        $this->c1 = new CasRec($data);
    }

    public function testDeleteHasRoleProtections()
    {
        $this->buildAndPersistCasRecEntity('12345678');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $url = '/casrec/delete';

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => false,
            'AuthToken' => self::$tokenAdmin,
            'assertResponseCode' => 200,
        ]);

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 403,
        ]);

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenProf,
            'assertResponseCode' => 403,
        ]);

        $this->assertJsonRequest('DELETE', $url, [
            'mustFail' => true,
            'AuthToken' => self::$tokenPa,
            'assertResponseCode' => 403,
        ]);
    }

    public function testDeleteBySourceDeletesBySource()
    {
        $this->buildAndPersistCasRecEntity('23410954');
        $this->buildAndPersistCasRecEntity('95043859');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $url = '/casrec/delete';

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $entitiesRemaining = $this->fixtures()->clear()->getRepo('CasRec')->findAll();
        $this->assertCount(0, $entitiesRemaining);
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
        $this->buildAndPersistCasRecEntity('12345678');
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

    private function buildAndPersistCasRecEntity(string $case): CasRec
    {
        $casRec = new CasRec([
            'Case' => $case,
            'ClientSurname' => 'I should get deleted',
            'DeputyUid' => 'Deputy No',
            'DeputySurname' => 'admin',
            'DeputyAddress1' => 'Victoria Road',
            'DeputyPostcode' => 'SW1',
            'ReportType' => 'OPG102',
            'MadeDate' => '2010-03-30',
            'OrderType' => 'pfa',
        ]);

        $this->fixtures()->persist($casRec);

        return $casRec;
    }
}

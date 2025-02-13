<?php

namespace App\Tests\Unit\Controller;

use App\Entity\PreRegistration;
use App\Entity\User;
use app\tests\Integration\Controller\AbstractTestController;
use app\tests\Integration\Fixtures;

class PreRegistrationControllerTest extends AbstractTestController
{
    private static User $deputy1;
    private static User $admin1;
    private static User $deputy2;
    private static $tokenAdmin;
    private static $tokenDeputy;
    private static $tokenProf;
    private static $tokenPa;

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
            'Hybrid' => 'SINGLE',
        ];

        $this->c1 = new PreRegistration($data);
    }

    public function testDeleteHasRoleProtections()
    {
        $this->buildAndPersistPreRegistrationEntity('12345678');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $url = '/pre-registration/delete';

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
        $this->buildAndPersistPreRegistrationEntity('23410954');
        $this->buildAndPersistPreRegistrationEntity('95043859');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $url = '/pre-registration/delete';

        $this->assertJsonRequest('DELETE', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);

        $entitiesRemaining = $this->fixtures()->clear()->getRepo('PreRegistration')->findAll();
        $this->assertCount(0, $entitiesRemaining);
    }

    public function testCount()
    {
        $url = '/pre-registration/count';
        $this->assertEndpointNeedsAuth('GET', $url);
        $this->assertEndpointNotAllowedFor('GET', $url, self::$tokenDeputy);

        Fixtures::deleteReportsData(['pre_registration']);
        $this->fixtures()->persist($this->c1)->flush($this->c1);

        // check count

        $data = $this->assertJsonRequest('GET', $url, [
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ])['data'];

        $this->assertEquals(1, $data);
    }

    public function testVerifyPreRegistration()
    {
        $this->buildAndPersistPreRegistrationEntity('12345678');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $this->assertJsonRequest('POST', '/pre-registration/verify', [
            'data' => [
                'case_number' => '12345678',
                'lastname' => 'I should get deleted',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenAdmin,
        ]);
    }

    public function testVerifyPreRegistrationCoDeputyCannotSignUp()
    {
        $deputy1 = $this->fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        $this->fixtures()->createClient($deputy1, ['setFirstname' => 'deputy1Client1', 'setCaseNumber' => '12345678']);

        $this->buildAndPersistPreRegistrationEntity('12345678', 'SINGLE', 'test', 'deputy');
        $this->buildAndPersistPreRegistrationEntity('12345678', 'SINGLE', 'cotest', 'codeputy');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        self::$tokenDeputy = $this->loginAsDeputy();

        /** @var User $loggedInUser */
        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        $this->fixtures()->persist($loggedInUser);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $this->assertJsonRequest('POST', '/pre-registration/verify', [
            'data' => [
                'case_number' => '12345678',
                'lastname' => 'I should get deleted',
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 403,
        ]);
    }

    private function buildAndPersistPreRegistrationEntity(string $case, string $hybrid = 'SINGLE', string $deputyFirstname = 'test', string $deputySurname = 'admin'): PreRegistration
    {
        $preRegistration = new PreRegistration([
            'Case' => $case,
            'ClientSurname' => 'I should get deleted',
            'DeputyUid' => '700571111000',
            'DeputyFirstname' => $deputyFirstname,
            'DeputySurname' => $deputySurname,
            'DeputyAddress1' => 'Victoria Road',
            'DeputyPostcode' => 'SW1',
            'ReportType' => 'OPG102',
            'MadeDate' => '2010-03-30',
            'OrderType' => 'pfa',
            'Hybrid' => $hybrid,
        ]);

        $this->fixtures()->persist($preRegistration);

        return $preRegistration;
    }

    public function testDeputyUidSetWhenSingleMatchFound()
    {
        $this->buildAndPersistPreRegistrationEntity('17171717', 'SINGLE', 'test', 'deputy');
        $this->buildAndPersistPreRegistrationEntity('28282828', 'SINGLE', 'test', 'deputy');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        self::$tokenDeputy = $this->loginAsDeputy();

        /** @var User $loggedInUser */
        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        $loggedInUser->setDeputyNo(null);
        $loggedInUser->setDeputyUid(0);
        $this->fixtures()->persist($loggedInUser);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $this->assertJsonRequest('POST', '/pre-registration/verify', [
            'data' => [
                'case_number' => '17171717',
                'lastname' => 'I should get deleted',
            ],
            'mustSucceed' => true,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        $this->assertEquals('700571111000', $loggedInUser->getDeputyNo());
        $this->assertEquals('700571111000', $loggedInUser->getDeputyUid());
        self::assertTrue($loggedInUser->getPreRegisterValidatedDate() instanceof \DateTime);
        self::assertTrue($loggedInUser->getIsPrimary());
    }

    public function testDeputyUidNotSetWhenMultipleMatchesFound()
    {
        $this->buildAndPersistPreRegistrationEntity('39393939', 'DUAL', 'test', 'deputy');
        $this->buildAndPersistPreRegistrationEntity('39393939', 'DUAL', 'test', 'deputy');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        self::$tokenDeputy = $this->loginAsDeputy();

        /** @var User $loggedInUser */
        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        $loggedInUser->setDeputyNo(null);
        $loggedInUser->setDeputyUid(0);
        $this->fixtures()->persist($loggedInUser);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        $this->assertJsonRequest('POST', '/pre-registration/verify', [
            'data' => [
                'case_number' => '39393939',
                'lastname' => 'I should get deleted',
            ],
            'mustSucceed' => false,
            'AuthToken' => self::$tokenDeputy,
        ]);

        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        try {
            $this->assertNull($loggedInUser->getDeputyNo());
            $this->assertEquals(0, $loggedInUser->getDeputyUid());
        } catch (\RuntimeException $e) {
            $expectedErrorMessage = 'A unique deputy record for case number 39393939 could not be identified';
            $this->assertEquals($expectedErrorMessage, $e->getMessage());
            $this->assertEquals(462, $e->getCode());
        }
    }

    public function testVerifySameDeputyCannotSignUp()
    {
        $deputy1 = $this->fixtures()->getRepo('User')->findOneByEmail('deputy@example.org');
        $this->fixtures()->createClient($deputy1, ['setFirstname' => 'deputy1Client1', 'setCaseNumber' => '1234567t']);

        $this->buildAndPersistPreRegistrationEntity('1234567t', 'SINGLE', 'test', 'deputy');
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        self::$tokenDeputy = $this->loginAsDeputy();

        /** @var User $loggedInUser */
        $loggedInUser = $this->fixtures()->clear()->getRepo('User')->find($this->loggedInUserId);

        $this->fixtures()->persist($loggedInUser);
        $this->fixtures()->flush();
        $this->fixtures()->clear();

        // Testing with lowercase t
        $this->assertJsonRequest('POST', '/pre-registration/verify', [
            'data' => [
                'case_number' => '1234567t',
                'lastname' => 'deputy',
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 425,
        ]);

        // Testing with uppercase T
        $this->assertJsonRequest('POST', '/pre-registration/verify', [
            'data' => [
                'case_number' => '1234567T',
                'lastname' => 'deputy',
            ],
            'mustFail' => true,
            'AuthToken' => self::$tokenDeputy,
            'assertResponseCode' => 425,
        ]);
    }
}

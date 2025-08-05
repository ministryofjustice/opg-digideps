<?php

namespace App\Tests\Integration\Controller;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\User;

class SelfRegisterControllerTest extends AbstractTestController
{
    public static function setUpBeforeClass(): void
    {
        // This is here to override to prevent the default setup until tests that fail with it are altered
    }

    public function setUp(): void
    {
        self::setupFixtures();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        self::fixtures()->clear();
    }

    /** @test */
    public function failsWhenMissingData()
    {
        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-missingdata@example.org',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);
    }

    /** @test */
    public function dontSaveInvalidUserToDB()
    {
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-dontsaveme@example.org',
                'client_firstname' => null,
                'client_lastname' => '',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $user = self::fixtures()->getRepo('User')->findOneBy(['email' => 'behat-dontsaveme@example.org']);
        $this->assertNull($user);
    }

    /** @test */
    public function dontSaveUserToDBWithInvalidCaseNumber()
    {
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $response = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'dontsavewithinvalidcasenum@example.org',
                'client_firstname' => 'James',
                'client_lastname' => 'Morrison',
                'case_number' => '123456789',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $user = self::fixtures()->getRepo('User')->findOneBy(['email' => 'dontsavewithinvalidcasenum@example.org']);

        $this->assertStringContainsString('Invalid registration data', $response['message']);
        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function savesValidUserToDb()
    {
        $preRegistration = $this->generatePreRegistration('12345678', 'Cross-Tolley', '700000019957', 'Zac', 'Tolley');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush($preRegistration);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $id = $responseArray['data']['id'];

        $user = self::fixtures()->getRepo('User')->findOneBy(['id' => $id]); /* @var $user User */
        $this->assertEquals('Tolley', $user->getLastname());
        $this->assertEquals('Zac', $user->getFirstname());
        $this->assertEquals('SW1', $user->getAddressPostcode());
        $this->assertEquals('gooduser@example.com', $user->getEmail());
        $this->assertEquals(true, $user->getNdrEnabled());

        /** @var Client $theClient */
        $theClient = $user->getClients()->first();

        $this->assertEquals('John', $theClient->getFirstname());
        $this->assertEquals('Cross-Tolley', $theClient->getLastname());
        $this->assertEquals('12345678', $theClient->getCaseNumber());
    }

    /** @test */
    public function saveUserToDBWithTruncated10DigitCaseNumber()
    {
        $preRegistration = $this->generatePreRegistration('12345677', 'Morrison', '700000019958', 'Zac', 'Tolley');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush($preRegistration);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'valid10digitcasenum@example.org',
                'client_firstname' => 'James',
                'client_lastname' => 'Morrison',
                'case_number' => '1234567790',
                'postcode' => 'SW1',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $id = $responseArray['data']['id'];

        $user = self::fixtures()->getRepo('User')->findOneBy(['id' => $id]); /* @var $user User */
        $this->assertEquals('Tolley', $user->getLastname());
        $this->assertEquals('Zac', $user->getFirstname());
        $this->assertEquals('valid10digitcasenum@example.org', $user->getEmail());

        /** @var Client $theClient */
        $theClient = $user->getClients()->first();

        $this->assertEquals('James', $theClient->getFirstname());
        $this->assertEquals('Morrison', $theClient->getLastname());
        $this->assertEquals('12345677', $theClient->getCaseNumber());
    }

    /**
     * @test
     *
     * @depends savesValidUserToDb
     */
    public function userNotFoundinPreRegistration()
    {
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'not found',
                'lastname' => 'test',
                'email' => 'gooduser2@example.org',
                'postcode' => 'SW2',
                'client_firstname' => 'Cf',
                'client_lastname' => 'Cl',
                'case_number' => '12345600',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $expectedErrorJson = json_encode([
            'search_terms' => [
                'caseNumber' => '12345600',
                'clientLastname' => 'Cl',
                'deputyFirstname' => 'not found',
                'deputyLastname' => 'test',
                'deputyPostcode' => 'SW2',
            ],
        ]);

        $this->assertStringContainsString($expectedErrorJson, $responseArray['message']);
    }

    /**
     * @test
     *
     * @depends savesValidUserToDb
     */
    public function throwErrorForDuplicate()
    {
        $preRegistration = $this->generatePreRegistration('12345678', 'Cross-Tolley', '700000019957', 'Zac', 'Tolley');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush($preRegistration);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser-new@example.org',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'assertResponseCode' => 425,
            'assertCode' => 425,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser1@example.org',
                'postcode' => 'SW1',
                'client_firstname' => 'Jonh',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678', // already taken !
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);
    }

    /**
     * @test
     */
    public function throwErrorForValidCaseNumberButDetailsNotMatching()
    {
        $now = new \DateTime();

        $preRegistration = $this->generatePreRegistration('97643164', 'Douglas', '700000019957', 'Ben', 'Murphy');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush();

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'wrong@example.org',
                'postcode' => 'ABC 123',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '97643164',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $expectedErrorJson = [
            'search_terms' => [
                'caseNumber' => '97643164',
                'clientLastname' => 'Cross-Tolley',
                'deputyFirstname' => 'Zac',
                'deputyLastname' => 'Tolley',
                'deputyPostcode' => 'ABC 123',
            ],
            'case_number_matches' => [
                [
                    'id' => 1,
                    'case_number' => '97643164',
                    'client_firstname' => null,
                    'client_lastname' => 'Douglas',
                    'client_address1' => null,
                    'client_address2' => null,
                    'client_address3' => null,
                    'client_address4' => null,
                    'client_address5' => null,
                    'client_postcode' => null,
                    'deputy_uid' => '700000019957',
                    'deputy_firstname' => 'Ben',
                    'deputy_surname' => 'Murphy',
                    'deputy_address1' => 'Victoria Road',
                    'deputy_address2' => null,
                    'deputy_address3' => null,
                    'deputy_address4' => null,
                    'deputy_address5' => null,
                    'deputy_post_code' => 'SW1',
                    'type_of_report' => 'OPG102',
                    'order_type' => 'pfa',
                    'updated_at' => null,
                    'order_date' => '2010-03-30T00:00:00+01:00',
                    'is_co_deputy' => null,
                    'ndr' => true,
                    'hybrid' => null,
                    'created_at' => $now->format('c'),
                ],
            ],
            'matching_errors' => [
                'client_lastname' => true,
                'deputy_firstname' => true,
                'deputy_lastname' => true,
                'deputy_postcode' => true,
            ],
        ];

        $this->assertEquals($expectedErrorJson, json_decode($responseArray['message'], true));
    }

    /**
     * @test
     */
    public function throwErrorForValidCaseNumberClientLastnameDeputyPostcodeButInvalidDeputyFirstname()
    {
        $now = new \DateTime();

        $preRegistration = $this->generatePreRegistration('97643164', 'Douglas', '700000019957', 'Stewart', 'Tolley');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush();

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'wrong@example.org',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Douglas',
                'case_number' => '97643164',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $expectedErrorJson = [
            'search_terms' => [
                'caseNumber' => '97643164',
                'clientLastname' => 'Douglas',
                'deputyFirstname' => 'Zac',
                'deputyLastname' => 'Tolley',
                'deputyPostcode' => 'SW1',
            ],
            'case_number_matches' => [
                [
                    'id' => 1,
                    'case_number' => '97643164',
                    'client_firstname' => null,
                    'client_lastname' => 'Douglas',
                    'client_address1' => null,
                    'client_address2' => null,
                    'client_address3' => null,
                    'client_address4' => null,
                    'client_address5' => null,
                    'client_postcode' => null,
                    'deputy_uid' => '700000019957',
                    'deputy_firstname' => 'Stewart',
                    'deputy_surname' => 'Tolley',
                    'deputy_address1' => 'Victoria Road',
                    'deputy_address2' => null,
                    'deputy_address3' => null,
                    'deputy_address4' => null,
                    'deputy_address5' => null,
                    'deputy_post_code' => 'SW1',
                    'type_of_report' => 'OPG102',
                    'order_type' => 'pfa',
                    'updated_at' => null,
                    'order_date' => '2010-03-30T00:00:00+01:00',
                    'is_co_deputy' => null,
                    'ndr' => true,
                    'hybrid' => null,
                    'created_at' => $now->format('c'),
                ],
            ],
            'matching_errors' => [
                'client_lastname' => false,
                'deputy_firstname' => true,
                'deputy_lastname' => false,
                'deputy_postcode' => false,
            ],
        ];

        $this->assertEquals($expectedErrorJson, json_decode($responseArray['message'], true));
    }

    /**
     * @test
     */
    public function throwErrorForValidCaseNumberClientLastnameDeputyPostcodeButInvalidDeputyLastname()
    {
        $now = new \DateTime();

        $preRegistration = $this->generatePreRegistration('97643164', 'Douglas', '700000019957', 'Zac', 'Murphy');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush();

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'wrong@example.org',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Douglas',
                'case_number' => '97643164',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $expectedErrorJson = [
            'search_terms' => [
                'caseNumber' => '97643164',
                'clientLastname' => 'Douglas',
                'deputyFirstname' => 'Zac',
                'deputyLastname' => 'Tolley',
                'deputyPostcode' => 'SW1',
            ],
            'case_number_matches' => [
                [
                    'id' => 1,
                    'case_number' => '97643164',
                    'client_firstname' => null,
                    'client_lastname' => 'Douglas',
                    'client_address1' => null,
                    'client_address2' => null,
                    'client_address3' => null,
                    'client_address4' => null,
                    'client_address5' => null,
                    'client_postcode' => null,
                    'deputy_uid' => '700000019957',
                    'deputy_firstname' => 'Zac',
                    'deputy_surname' => 'Murphy',
                    'deputy_address1' => 'Victoria Road',
                    'deputy_address2' => null,
                    'deputy_address3' => null,
                    'deputy_address4' => null,
                    'deputy_address5' => null,
                    'deputy_post_code' => 'SW1',
                    'type_of_report' => 'OPG102',
                    'order_type' => 'pfa',
                    'updated_at' => null,
                    'order_date' => '2010-03-30T00:00:00+01:00',
                    'is_co_deputy' => null,
                    'ndr' => true,
                    'hybrid' => null,
                    'created_at' => $now->format('c'),
                ],
            ],
            'matching_errors' => [
                'client_lastname' => false,
                'deputy_firstname' => false,
                'deputy_lastname' => true,
                'deputy_postcode' => false,
            ],
        ];

        $this->assertEquals($expectedErrorJson, json_decode($responseArray['message'], true));
    }

    /**
     * @test
     */
    public function throwErrorForValidCaseNumberClientLastnameAndDeputyFirstAndLastnameButInvalidPostcode()
    {
        $now = new \DateTime();

        $preRegistration = $this->generatePreRegistration('97643164', 'Douglas', '700000019957', 'Zac', 'Murphy');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush();

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Murphy',
                'email' => 'wrong@example.org',
                'postcode' => 'ABC 123',
                'client_firstname' => 'John',
                'client_lastname' => 'Douglas',
                'case_number' => '97643164',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $expectedErrorJson = [
            'search_terms' => [
                'caseNumber' => '97643164',
                'clientLastname' => 'Douglas',
                'deputyFirstname' => 'Zac',
                'deputyLastname' => 'Murphy',
                'deputyPostcode' => 'ABC 123',
            ],
            'case_number_matches' => [
                [
                    'id' => 1,
                    'case_number' => '97643164',
                    'client_firstname' => null,
                    'client_lastname' => 'Douglas',
                    'client_address1' => null,
                    'client_address2' => null,
                    'client_address3' => null,
                    'client_address4' => null,
                    'client_address5' => null,
                    'client_postcode' => null,
                    'deputy_uid' => '700000019957',
                    'deputy_firstname' => 'Zac',
                    'deputy_surname' => 'Murphy',
                    'deputy_address1' => 'Victoria Road',
                    'deputy_address2' => null,
                    'deputy_address3' => null,
                    'deputy_address4' => null,
                    'deputy_address5' => null,
                    'deputy_post_code' => 'SW1',
                    'type_of_report' => 'OPG102',
                    'order_type' => 'pfa',
                    'updated_at' => null,
                    'order_date' => '2010-03-30T00:00:00+01:00',
                    'is_co_deputy' => null,
                    'ndr' => true,
                    'hybrid' => null,
                    'created_at' => $now->format('c'),
                ],
            ],
            'matching_errors' => [
                'client_lastname' => false,
                'deputy_firstname' => false,
                'deputy_lastname' => false,
                'deputy_postcode' => true,
            ],
        ];

        $this->assertEquals($expectedErrorJson, json_decode($responseArray['message'], true));
    }

    public function generatePreRegistration(string $caseNumber, string $clientSurname, string $deputyUid, string $deputyFirstname, string $deputySurname, ?\DateTime $createdAt = null): PreRegistration
    {
        return new PreRegistration([
            'Case' => $caseNumber,
            'ClientSurname' => $clientSurname,
            'DeputyUid' => $deputyUid,
            'DeputyFirstname' => $deputyFirstname,
            'DeputySurname' => $deputySurname,
            'DeputyAddress1' => 'Victoria Road',
            'DeputyPostcode' => 'SW1',
            'ReportType' => 'OPG102',
            'MadeDate' => '2010-03-30',
            'OrderType' => 'pfa',
            'NDR' => 'yes',
        ]);
    }

    /**
     * @test
     */
    public function testDeputiesNonPrimaryAccountSetToFalse()
    {
        $this->generateDeputyPrimaryAccount();
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        // create second deputy account
        $preRegistration2 = $this->generatePreRegistration('23456789', 'Jones', '700000019965', 'Zac', 'Tolley');

        $this->fixtures()->persist($preRegistration2);
        $this->fixtures()->flush($preRegistration2);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'seconddeputyaccount@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'Jamie',
                'client_lastname' => 'Jones',
                'case_number' => '23456789',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $id = $responseArray['data']['id'];

        $user = self::fixtures()->getRepo('User')->findOneBy(['id' => $id]);
        $this->assertEquals('700000019965', $user->getDeputyUid());
        $this->assertFalse($user->getIsPrimary());
    }

    /**
     * @test
     */
    public function testNoExistingAccountsAreIdentifiedForCoDeputyWithSingleAccount()
    {
        $this->generateDeputyAndCoDeputyPreRegistration(null);

        // deputy logs in ang registers
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'deputy1@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $deputyId = $responseArray['data']['id'];
        $deputy = self::fixtures()->getRepo('User')->findOneBy(['id' => $deputyId]);
        $this->assertTrue($deputy->getIsPrimary());

        $coDeputy = $this->fixtures()->createUser();
        $coDeputy->setFirstName('Sue');
        $coDeputy->setLastName('Jones');
        $coDeputy->setEmail('codeputy1@example.com');
        $coDeputy->setCreatedBy($deputy);
        $coDeputy->setRegistrationRoute('CO_DEPUTY_INVITE');

        $this->fixtures()->persist($coDeputy);
        $this->fixtures()->flush();

        $responseArray = $this->assertJsonRequest('POST', '/selfregister/verifycodeputy', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Sue',
                'lastname' => 'Jones',
                'email' => 'codeputy1@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $existingDeputyAccounts = $responseArray['data']['existingDeputyAccounts'];
        $this->assertEmpty($existingDeputyAccounts);
    }

    /**
     * @test
     */
    public function testExistingAccountsAreIdentifiedForCoDeputyWithASecondAccount()
    {
        // first registered deputy account
        $deputyPrimaryAccountUid = $this->generateDeputyPrimaryAccount();

        // new deputy and co-deputy pre-registration (which will be the second account for co-deputy)
        $this->generateDeputyAndCoDeputyPreRegistration($deputyPrimaryAccountUid);

        // deputy logs in ang registers before inviting co-deputy
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'deputy2@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $deputyId = $responseArray['data']['id'];
        $deputy = self::fixtures()->getRepo('User')->findOneBy(['id' => $deputyId]);
        $this->assertTrue($deputy->getIsPrimary());

        $coDeputy = $this->fixtures()->createUser();
        $coDeputy->setFirstName('Sue');
        $coDeputy->setLastName('Jones');
        $coDeputy->setEmail('codeputy2@example.com');
        $coDeputy->setCreatedBy($deputy);
        $coDeputy->setRegistrationRoute('CO_DEPUTY_INVITE');

        $this->fixtures()->persist($coDeputy);
        $this->fixtures()->flush();

        $responseArray = $this->assertJsonRequest('POST', '/selfregister/verifycodeputy', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Sue',
                'lastname' => 'Jones',
                'email' => 'codeputy2@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $existingDeputyAccounts = $responseArray['data']['existingDeputyAccounts'];
        $this->assertNotEmpty($existingDeputyAccounts);
    }

    /**
     * @test
     */
    public function testCoDeputyIsUpdatedWithVerificationData()
    {
        $deputyPreRegistration = $this->generateDeputyAndCoDeputyPreRegistration(null);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'deputy3@example.com',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $deputyId = $responseArray['data']['id'];
        $deputy = self::fixtures()->getRepo('User')->findOneBy(['id' => $deputyId]);

        $this->assertEquals($deputyPreRegistration->getDeputyUid(), $deputy->getDeputyUid());
        $this->assertTrue($deputy->getIsPrimary());

        $coDeputy = $this->fixtures()->createUser();
        $coDeputy->setFirstName('Sue');
        $coDeputy->setLastName('Jones');
        $coDeputy->setEmail('codeputy3@example.com');
        $coDeputy->setCreatedBy($deputy);
        $coDeputy->setRegistrationRoute('CO_DEPUTY_INVITE');

        $this->fixtures()->persist($coDeputy);
        $this->fixtures()->flush();

        $responseArray = $this->assertJsonRequest('PUT', '/selfregister/updatecodeputy/'.$coDeputy->getId(), [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'verified' => true,
                'coDeputyUid' => '700000019951',
                'existingDeputyAccounts' => null,
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $response = $responseArray['data'];

        $this->assertTrue($response['co_deputy_client_confirmed']);
        $this->assertEquals('700000019951', $response['deputy_no']);
        $this->assertEquals('700000019951', $response['deputy_uid']);
        $this->assertNotEmpty($response['registration_date']);
        $this->assertNotEmpty($response['pre_register_validated_date']);
        $this->assertTrue($response['active']);
        $this->assertTrue($response['is_primary']);
    }

    private function generateDeputyAndCoDeputyPreRegistration($deputyPrimaryUid)
    {
        $deputyUid = intval('7'.str_pad((string) mt_rand(1, 99999999), 11, '0', STR_PAD_LEFT));

        $deputyPreRegistration = $this->generatePreRegistration('12345678', 'Cross-Tolley', $deputyUid, 'Zac', 'Tolley');
        $deputyPreRegistration->setIsCoDeputy(true);

        if ($deputyPrimaryUid) {
            $coDeputyPreRegistration = $this->generatePreRegistration('12345678', 'Cross-Tolley', $deputyPrimaryUid, 'Sue', 'Jones');
        } else {
            $coDeputyUid = intval('7'.str_pad((string) mt_rand(1, 99999999), 11, '0', STR_PAD_LEFT));
            $coDeputyPreRegistration = $this->generatePreRegistration('12345678', 'Cross-Tolley', $coDeputyUid, 'Sue', 'Jones');
        }
        $coDeputyPreRegistration->setIsCoDeputy(true);

        $this->fixtures()->persist($coDeputyPreRegistration);
        $this->fixtures()->persist($deputyPreRegistration);
        $this->fixtures()->flush();

        return $deputyPreRegistration;
    }

    private function generateDeputyPrimaryAccount()
    {
        $casenumber = strval(mt_rand(10000000, 99999999));
        $preRegistration = $this->generatePreRegistration($casenumber, 'Smith', '700000019965', 'Sue', 'Jones');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush($preRegistration);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Sue',
                'lastname' => 'Jones',
                'email' => sprintf('firstdeputy%s@example.com', $casenumber),
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Smith',
                'case_number' => $casenumber,
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        return $responseArray['data']['deputy_uid'];
    }
}

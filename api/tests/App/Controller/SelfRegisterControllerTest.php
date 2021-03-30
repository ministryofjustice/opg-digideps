<?php

namespace Tests\App\Controller;

use App\Entity\CasRec;
use App\Entity\User;
use Mockery as m;

class SelfRegisterControllerTest extends AbstractTestController
{
    /** @test */
    public function failsWhenMissingData()
    {
        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-missingdata@gov.uk',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);
    }

    /** @test */
    public function dontSaveUnvalidUserToDB()
    {
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-dontsaveme@uk.gov',
                'client_firstname' => '',
                'client_lastname' => '',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $user = self::fixtures()->getRepo('User')->findOneBy(['email' => 'behat-dontsaveme@uk.gov']);
        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function savesValidUserToDb()
    {
        $casRec = new CasRec([
            'Case' => '12345678',
            'Surname' => 'Cross-Tolley',
            'Deputy No' => 'DEP0011',
            'Dep Surname' => 'Tolley',
            'Dep Postcode' => 'SW1',
            'Typeofrep'=>'OPG102',
            'Corref'=>'L2',
            'NDR' => 1
        ]);

        $this->fixtures()->persist($casRec);
        $this->fixtures()->flush($casRec);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser@gov.zzz',
                'postcode' => 'SW1',
                'client_firstname' => 'John',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $id = $responseArray['data']['id'];

        $user = self::fixtures()->getRepo('User')->findOneBy(['id' => $id]); /** @var $user User */
        $this->assertEquals('Tolley', $user->getLastname());
        $this->assertEquals('Zac', $user->getFirstname());
        $this->assertEquals('SW1', $user->getAddressPostcode());
        $this->assertEquals('gooduser@gov.zzz', $user->getEmail());
        $this->assertEquals(true, $user->getNdrEnabled());

        /** @var \App\Entity\Client $theClient */
        $theClient = $user->getClients()->first();

        $this->assertEquals('John', $theClient->getFirstname());
        $this->assertEquals('Cross-Tolley', $theClient->getLastname());
        $this->assertEquals('12345678', $theClient->getCaseNumber());
    }

    /**
     * @test
     * @depends savesValidUserToDb
     */
    public function userNotFoundinCasRec()
    {
        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'not found',
                'lastname' => 'test',
                'email' => 'gooduser2@gov.zzz',
                'postcode' => 'SW2',
                'client_firstname' => 'Cf',
                'client_lastname' => 'Cl',
                'case_number' => '12345600',
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);

        $this->assertStringContainsString('no matching record in casrec', $responseArray['message']);
    }

    /**
     * @test
     * @depends savesValidUserToDb
     */
    public function throwErrorForDuplicate()
    {
        $casRec = new CasRec([
            'Case' => '12345678',
            'Surname' => 'Cross-Tolley',
            'Deputy No' => 'DEP0011',
            'Dep Surname' => 'Tolley',
            'Dep Postcode' => 'SW1',
            'Typeofrep'=>'OPG102',
            'Corref'=>'L2',
            'NDR' => 1
        ]);

        $this->fixtures()->persist($casRec);
        $this->fixtures()->flush($casRec);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser@gov.abc',
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
                'email' => 'gooduser1@gov.abc',
                'postcode' => 'SW1',
                'client_firstname' => 'Jonh',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678', // already taken !
            ],
            'ClientSecret' => API_TOKEN_DEPUTY,
        ]);
    }
}

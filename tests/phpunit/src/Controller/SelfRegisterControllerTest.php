<?php

namespace AppBundle\Controller;

use AppBundle\Model\SelfRegisterData;
use AppBundle\Entity\CasRec;
use Mockery as m;

class SelfRegisterControllerTest extends AbstractTestController
{
    /** @var SelfRegisterController */
    private $selfRegisterController;

    public function setUp()
    {
        $this->selfRegisterController = new SelfRegisterController();
        parent::setUp();
//        self::$frameworkBundleClient = static::createClient([ 'environment' => 'test','debug' => true]);
//        $this->em = self::$frameworkBundleClient->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    /** @test */
    public function populateUser()
    {
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'behat-test@gov.uk',
            'postcode' => 'SW1',
            'client_lastname' => 'Cross-Tolley',
            'case_number' => '12345678',
        ];

        $selfRegisterData = new SelfRegisterData();

        $this->selfRegisterController->populateSelfReg($selfRegisterData, $data);

        $this->assertEquals('Zac', $selfRegisterData->getFirstname());
        $this->assertEquals('Tolley', $selfRegisterData->getLastname());
        $this->assertEquals('behat-test@gov.uk', $selfRegisterData->getEmail());
        $this->assertEquals('SW1', $selfRegisterData->getPostcode());
        $this->assertEquals('Cross-Tolley', $selfRegisterData->getClientLastname());
        $this->assertEquals('12345678', $selfRegisterData->getCaseNumber());
    }

    /** @test */
    public function populatePartialData()
    {
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'zac@thetolleys.com',
        ];

        $selfRegisterData = new SelfRegisterData();

        $this->selfRegisterController->populateSelfReg($selfRegisterData, $data);

        $this->assertEquals('Zac', $selfRegisterData->getFirstname());
        $this->assertEquals('Tolley', $selfRegisterData->getLastname());
        $this->assertEquals('zac@thetolleys.com', $selfRegisterData->getEmail());
    }

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
            'ClientSecret' => '123abc-deputy',
        ]);
    }

    /** @test */
    public function dontSaveUnvalidUserToDB()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-dontsaveme@uk.gov',
                'client_lastname' => '',
                'case_number' => '12345678',
            ],
            'ClientSecret' => '123abc-deputy',
        ]);

        $user = self::fixtures()->getRepo('User')->findOneBy(['email' => 'behat-dontsaveme@uk.gov']);
        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function savesValidUserToDb()
    {
        self::$frameworkBundleClient->request('GET', '/'); // warm up to get container

        $casRec = new CasRec('12345678', 'Cross-Tolley', 'DEP001', 'Tolley', 'SW1');
        $this->fixtures()->persist($casRec);
        $this->fixtures()->flush($casRec);

        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser@gov.zzz',
                'postcode' => 'SW1',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678',
            ],
            'ClientSecret' => '123abc-deputy',
        ]);

        $id = $responseArray['data']['id'];

        $user = self::fixtures()->getRepo('User')->findOneBy(['id' => $id]);
        $this->assertEquals('Tolley', $user->getLastname());
        $this->assertEquals('Zac', $user->getFirstname());
        $this->assertEquals('SW1', $user->getAddressPostcode());
        $this->assertEquals('gooduser@gov.zzz', $user->getEmail());

        /** @var \AppBundle\Entity\Client $theClient */
        $theClient = $user->getClients()->first();

        $this->assertEquals('Cross-Tolley', $theClient->getLastname());
        $this->assertEquals('12345678', $theClient->getCaseNumber());
    }

    /**
     * @test
     * @depends savesValidUserToDb
     */
    public function userNotFoundinCasRec()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        $responseArray = $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'not found',
                'lastname' => 'test',
                'email' => 'gooduser2@gov.zzz',
                'postcode' => 'SW2',
                'client_lastname' => 'Cl',
                'case_number' => '12345600',
            ],
            'ClientSecret' => '123abc-deputy',
        ]);

        $this->assertContains('not found', $responseArray['message']);
    }

    /** 
     * @test 
     * @depends savesValidUserToDb
     */
    public function throwErrorForDuplicate()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');

        $this->assertJsonRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'assertResponseCode' => 425,
            'assertCode' => 425,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser1@gov.zzz',
                'postcode' => 'SW1',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12345678', // already taken !
            ],
            'ClientSecret' => '123abc-deputy',
        ]);
    }
}

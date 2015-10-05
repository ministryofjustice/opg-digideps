<?php

namespace AppBundle\Controller;

use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;
use AppBundle\Model\SelfRegisterData;
use Mockery as m;

class SelfRegisterControllerTest extends AbstractTestController
{

    /** @var SelfRegisterController */
    private $selfRegisterController;


    public function setUp()
    {
        $this->selfRegisterController = new SelfRegisterController();
        parent::setUp();
//        $this->frameworkBundleClient = static::createClient([ 'environment' => 'test','debug' => true]);
//        $this->em = $this->frameworkBundleClient->getContainer()->get('doctrine.orm.entity_manager');
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
            'client_lastname' => 'Cross-Tolley',
            'case_number' => '12341234'
        ];

        $selfRegisterData = new SelfRegisterData();

        $this->selfRegisterController->populateSelfReg($selfRegisterData, $data);

        $this->assertEquals('Zac', $selfRegisterData->getFirstname());
        $this->assertEquals('Tolley', $selfRegisterData->getLastname());
        $this->assertEquals('behat-test@gov.uk', $selfRegisterData->getEmail());
        $this->assertEquals('Cross-Tolley', $selfRegisterData->getClientLastname());
        $this->assertEquals('12341234', $selfRegisterData->getCaseNumber());
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
        $this->assertRequest('POST', '/selfregister', [
            'mustFail' => true,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-missingdata@gov.uk',
            ],
            'ClientSecret' => '123abc-deputy'
        ]);
    }


    /** @test */
    public function dontSaveUnvalidUserToDB()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $this->assertRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'behat-dontsaveme@uk.gov',
                'client_lastname' => '',
                'case_number' => '12341234'
            ],
            'ClientSecret' => '123abc-deputy'
        ]);

        $user = $this->fixtures->getRepo('User')->findOneBy(['email' => 'behat-dontsaveme@uk.gov']);
        $this->assertNull($user);
    }


    /**
     * @test
     */
    public function savesValidUserToDb()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        $responseArray = $this->assertRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => [
                'firstname' => 'Zac',
                'lastname' => 'Tolley',
                'email' => 'gooduser@gov.zzz',
                'client_lastname' => 'Cross-Tolley',
                'case_number' => '12341234'
            ],
            'ClientSecret' => '123abc-deputy'
        ]);

        $id = $responseArray['data']['id'];

        /** @var /AppBundle/Entity/User $user */
        $user = $this->fixtures->getRepo('User')->findOneBy(['id' => $id]);

        $this->assertEquals('Tolley', $user->getLastname());
        $this->assertEquals('Zac', $user->getFirstname());
        $this->assertEquals('gooduser@gov.zzz', $user->getEmail());

        /** @var \AppBundle\Entity\Client $theClient */
        $theClient = $user->getClients()->first();

        $this->assertEquals("Cross-Tolley", $theClient->getLastname());
        $this->assertEquals('12341234', $theClient->getCaseNumber());
    }


    /** @test */
    public function throwErrorForDuplicate()
    {
        $token = $this->login('deputy@example.org', 'Abcd1234', '123abc-deputy');
        
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'duplicate@uk.zzz',
            'client_lastname' => 'Cross-Tolley',
            'case_number' => '12341234'
        ];
        
        // 1st one succeed
        $this->assertRequest('POST', '/selfregister', [
            'mustSucceed' => true,
            'AuthToken' => $token,
            'data' => $data,
            'ClientSecret' => '123abc-deputy'
        ]);
        
        //2nd fail (duplicate)
        $this->assertRequest('POST', '/selfregister', [
            'mustFail' => true,
            'AuthToken' => $token,
            'data' => $data,
            'ClientSecret' => '123abc-deputy'
        ]);
        
    }

}
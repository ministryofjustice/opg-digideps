<?php
namespace AppBundle\Service;

use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;
use AppBundle\Controller\SelfRegisterController;
use AppBundle\Model\SelfRegisterData;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Mockery as m;

class SelfRegisterControllerTest extends WebTestCase
{

    /** @var SelfRegisterController */
    private $selfRegisterController;

    /** @var Client  */
    private $client;

    /** @var EntityManager */
    private $em;

    public function setUp()
    {
        $this->selfRegisterController = new SelfRegisterController();
        $this->client = static::createClient([ 'environment' => 'test','debug' => true]);
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function tearDown()
    {
        m::close();
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
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'behat-missingdata@gov.uk',
        ];

        $this->client->request(
            'POST', '/selfregister',
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $responseArray = json_decode($this->client->getResponse()->getContent(),true);

        $this->assertFalse($responseArray['success']);
    }

    /** @test */
    public function dontSaveUnvalidUserToDB() {
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'behat-dontsaveme@uk.gov',
            'client_lastname' => '',
            'case_number' => '12341234'
        ];

        $this->client->request(
            'POST', '/selfregister',
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $user = $this->em->getRepository('AppBundle\Entity\User')->findOneBy(['email'=>'behat-dontsaveme@uk.gov']);
        $this->assertNull($user);

    }

    /**
     * @test
     */
    public function savesValidUserToDb() {
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'gooduser@gov.zzz',
            'client_lastname' => 'Cross-Tolley',
            'case_number' => '12341234'
        ];

        $this->client->request(
            'POST', '/selfregister',
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $responseArray = json_decode($this->client->getResponse()->getContent(),true);

        if ($responseArray['success'] == false) {
            echo $this->client->getResponse()->getContent();
        }

        $this->assertTrue($responseArray['success']);

        $id = $responseArray['data']['id'];

        $this->em->clear();

        /** @var /AppBundle/Entity/User $user */
        $user = $this->em->getRepository('AppBundle\Entity\User')->findOneBy(['id'=>$id]);

        $this->assertEquals('Tolley',$user->getLastname());
        $this->assertEquals('Zac',$user->getFirstname());
        $this->assertEquals('gooduser@gov.zzz',$user->getEmail());

        /** @var \AppBundle\Entity\Client $theClient */
        $theClient = $user->getClients()->first();

        $this->assertEquals("Cross-Tolley", $theClient->getLastname());
        $this->assertEquals('12341234', $theClient->getCaseNumber());

    }

    /** @test */
    public function throwErrorForDuplicate() {
        $data = [
            'firstname' => 'Zac',
            'lastname' => 'Tolley',
            'email' => 'duplicate@uk.zzz',
            'client_lastname' => 'Cross-Tolley',
            'case_number' => '12341234'
        ];

        $this->client->request(
            'POST', '/selfregister',
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $responseArray = json_decode($this->client->getResponse()->getContent(),true);

        if ($responseArray['success'] == false) {
            echo $this->client->getResponse()->getContent();
        }

        $this->assertTrue($responseArray['success']);

        $this->client->request(
            'POST', '/selfregister',
            array(), array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data)
        );

        $responseArray = json_decode($this->client->getResponse()->getContent(),true);
        $this->assertFalse($responseArray['success']);
    }

}
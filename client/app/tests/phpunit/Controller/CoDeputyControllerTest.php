<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Tests\Unit\Controller\AbstractTestController;

class CoDeputyControllerTest extends AbstractTestController
{
    /** @test */
    public function savesValidUserToDb()
    {
        $preRegistration = $this->generatePreRegistration('12345678', 'Cross-Tolley', '700000019957', 'Zac', 'Tolley');

        $this->fixtures()->persist($preRegistration);
        $this->fixtures()->flush($preRegistration);

        $token = $this->login('deputy@example.org', 'DigidepsPass1234', API_TOKEN_DEPUTY);

        $responseArray = $this->assertJsonRequest('POST', '/selfregister/verifycodeputy', [
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
        self::assertTrue($user->getPreRegisterValidatedDate() instanceof \DateTime);
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
        ], $createdAt);
    }
}

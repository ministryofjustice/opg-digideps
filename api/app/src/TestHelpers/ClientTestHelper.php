<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class ClientTestHelper extends TestCase
{
    public function createClientMock(int $id, bool $hasReports)
    {
        $report = $hasReports ? (self::prophesize(Report::class))->reveal() : null;

        $client = self::prophesize(Client::class);
        $client->getReports()->willReturn($report);
        $client->getId()->willReturn($id);

        return $client->reveal();
    }

    public function generateClient(EntityManager $em, User $user = null, Organisation $organisation = null, string $caseNumber = null): Client
    {
        $faker = Factory::create('en_GB');
        $config = [
            'fistName' => $faker->firstName(),
            'lastName' => $faker->lastName(),
            'caseNumber' => $caseNumber ?: self::createValidCaseNumber(),
            'email' => $faker->safeEmail().mt_rand(1, 100),
            'streetAddress' => $faker->streetAddress(),
            'postcode' => $faker->postcode(),
        ];

        return $this->createClient($em, $config);
    }
    
    public function generateClientFromArray(EntityManager $em, array $config): Client
    {
        $faker = Factory::create('en_GB');
        $config['email'] = $faker->safeEmail().mt_rand(1, 100);
        
        $configCorrect = match (true) {
            !empty($config['fistName']),
            !empty($config['lastName']),
            !empty($config['streetAddress']),
            !empty($config['postcode']) => true
        };
        
        return $this->createClient($em, $config);
    }
    
    protected function createClient(EntityManager $em, array $config): Client
    {
        $client = (new Client())
            ->setFirstname($config['firstName'])
            ->setLastname($config['lastName'])
            ->setCaseNumber($config['caseNumber'] ?: self::createValidCaseNumber())
            ->setEmail($config['email'])
            ->setCourtDate(new \DateTime('09-Aug-2018'))
            ->setAddress($config['streetAddress'])
            ->setAddress2($config['streetAddress'])
            ->setPostcode($config['postcode']);

        if ($config['user'] instanceof User && User::ROLE_LAY_DEPUTY === $config['user']->getRoleName()) {
            return $client->addUser($config['user'] ?: (new UserTestHelper())->createAndPersistUser($em));
        }

        if ($config['organisation'] instanceof Organisation) {
            return $client->setOrganisation($config['organisation']);
        }
        
        return $client;
    }

    /**
     * Sirius has a modulus 11 validation check on case references (because casrec.) which we should adhere to
     * to make sure integration tests create data that is in the correct format.
     */
    public static function createValidCaseNumber()
    {
        $ref = '';
        $sum = 0;

        foreach ([3, 4, 7, 5, 8, 2, 4] as $constant) {
            $value = mt_rand(0, 9);
            $ref .= $value;
            $sum += $value * $constant;
        }

        $checkbit = (11 - ($sum % 11)) % 11;

        if (10 === $checkbit) {
            $checkbit = 'T';
        }

        return $ref.$checkbit;
    }
}

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
use Prophecy\Prophet;

class ClientTestHelper extends TestCase
{
    public static function createClientMock(int $id, bool $hasReports)
    {
        $prophet = new Prophet();
        $report = $hasReports ? ($prophet->prophesize(Report::class))->reveal() : null;
        
        /** @var ObjectProphecy<Client> $client */
        $client = $prophet->prophesize(Client::class);
        $client->getReports()->willReturn($report);
        $client->getId()->willReturn($id);

        return $client->reveal();
    }

    public static function generateClient(EntityManager $em, User $user = null, Organisation $organisation = null, string $caseNumber = null)
    {
        $faker = Factory::create('en_GB');

        $client = (new Client())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setCaseNumber($caseNumber ?: self::createValidCaseNumber())
            ->setEmail($faker->safeEmail().mt_rand(1, 100))
            ->setCourtDate(new \DateTime('09-Aug-2018'))
            ->setAddress($faker->streetAddress())
            ->setAddress2($faker->streetAddress())
            ->setPostcode($faker->postcode());

        if (!is_null($user) && User::ROLE_LAY_DEPUTY === $user->getRoleName()) {
            return $client->addUser($user ?: (new UserTestHelper())->createAndPersistUser($em));
        }

        if ($organisation) {
            return $client->setOrganisation($organisation);
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

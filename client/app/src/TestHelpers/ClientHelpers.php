<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\Report\Report;
use DateTime;
use Faker\Factory;

class ClientHelpers
{
    public static function createClient(?Report $report = null): Client
    {
        $faker = Factory::create();

        $client = (new Client())
            ->setCaseNumber(self::createValidCaseNumber())
            ->setCourtDate(new DateTime())
            ->setEmail($faker->safeEmail())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setId(1);

        if ($report) {
            $client->addReport($report);
        }

        return $client;
    }

    public static function createClientWithUsers(?Report $report = null): Client
    {
        $user = UserHelpers::createUser();

        return (self::createClient($report))->addUser($user);
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

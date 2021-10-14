<?php

declare(strict_types=1);

namespace App\TestHelpers;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\User;
use App\FixtureFactory\CasRecFactory;
use App\Service\DateTimeProvider;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CaseTestHelper extends TestCase
{
    private $casRecFactory;

    public function generateCase(EntityManager $em, string $reportType)
    {
        $faker = Factory::create('en_GB');

        $casrecData = [
            'caseNumber' => self::createValidCaseNumber(),
            'clientLastName' => $faker->lastName,
            'reportType' => $reportType,
            'deputyPostCode' => $faker->postcode,
            'deputyLastName' => $faker->lastName,
        ];

        return $this->getFactory()->create($casrecData);
    }

    public function generateCaseFromClientAndUser(EntityManager $em, Client $client, User $deputy, string $reportType, ?NamedDeputy $namedDeputy = null)
    {
        $casrecData = [
            'caseNumber' => $client->getCaseNumber(),
            'clientLastName' => $client->getLastname(),
            'reportType' => $reportType,
            'deputyPostCode' => $deputy->getAddressPostcode(),
            'deputyLastName' => $deputy->getLastname(),
        ];

        if (!is_null($namedDeputy)) {
            $casrecData['namedDeputy'] = $namedDeputy->getDeputyNo();
        }

        return $this->getFactory()->create($casrecData);
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

    private function getFactory()
    {
        if (!is_null($this->casRecFactory)) {
            return $this->casRecFactory;
        } else {
            $validator = $this->createMock(ValidatorInterface::class);
            $dateTimeProvider = $this->createMock(DateTimeProvider::class);

            $validator
                ->expects($this->once())
                ->method('validate')
                ->willReturn(new ConstraintViolationList());

            $dateTimeProvider
                ->expects($this->once())
                ->method('getDateTime')
                ->willReturn(new \DateTime('2010-01-03 12:03:23'));

            $factory = new \App\v2\Registration\SelfRegistration\Factory\CasRecFactory($validator, $dateTimeProvider);

            return new CasRecFactory($factory);
        }
    }
}

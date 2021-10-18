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

class CourtOrderTestHelper extends TestCase
{
    private $casRecFactory;

    public function generateCourtOrder(EntityManager $em, string $reportType)
    {
        $faker = Factory::create('en_GB');

        $casrecData = [
            'caseNumber' => ClientTestHelper::createValidCaseNumber(),
            'clientLastName' => $faker->lastName,
            'reportType' => $reportType,
            'deputyPostCode' => $faker->postcode,
            'deputyLastName' => $faker->lastName,
        ];

        return $this->getFactory()->create($casrecData);
    }

    public function generateCourtOrderFromClientAndUser(EntityManager $em, Client $client, User $deputy, string $reportType, ?NamedDeputy $namedDeputy = null)
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

<?php declare(strict_types=1);

namespace Tests\AppBundle\v2\Registration\TestHelpers;

use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\v2\Registration\Assembler\CasRecToOrgDeputyshipDtoAssembler;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use Faker\Factory;
use Faker\Provider\en_GB\Address;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Serializer;

class OrgDeputyshipTestHelper
{
    public function setUp(): void
    {
    }

    public static function generateOrgDeputyshipJson(int $validCount, int $invalidCount)
    {
        $deputyships = [];

        if ($validCount > 0) {
            foreach (range(1, $validCount) as $index) {
                $deputyships[] = self::generateValidOrgDeputyshipArray();
            }
        }

        if ($invalidCount > 0) {
            foreach (range(1, $invalidCount) as $index) {
                $deputyships[] = self::generateInvalidOrgDeputyshipArray();
            }
        }

        return json_encode($deputyships);
    }


    /**
     * @param int $validCount
     * @param int $invalidCount
     * @return OrgDeputyshipDto[]
     */
    public static function generateOrgDeputyshipDtos(int $validCount, int $invalidCount)
    {
        $json = self::generateOrgDeputyshipJson($validCount, $invalidCount);
        $dtos = [];
        $assembler = new CasRecToOrgDeputyshipDtoAssembler();

        foreach (json_decode($json, true) as $dtoArray) {
            $dtos[] = $assembler->assembleFromArray($dtoArray);
        }

        return $dtos;
    }

    /**
     * @param int $clients
     * @param int $namedDeputies
     * @param int $reports
     * @param int $errors
     * @return false|string
     * @throws \Exception
     */
    public static function generateOrgDeputyshipResponseJson(int $clients, int $discharged, int $namedDeputies, int $reports, int $errors)
    {
        $result = ['errors' => $errors];
        $added = ['clients' => [], 'discharged_clients' => [], 'named_deputies' => [], 'reports' => []];

        if ($clients > 0) {
            foreach (range(1, $clients) as $index) {
                $added['clients'] = random_int(10000000, 99999999);
            }
        }

        if ($discharged > 0) {
            foreach (range(1, $discharged) as $index) {
                $added['discharged_clients'] = random_int(10000000, 99999999);
            }
        }

        if ($namedDeputies > 0) {
            foreach (range(1, $namedDeputies) as $index) {
                $added['named_deputies'] = random_int(10000000, 99999999);
            }
        }

        if ($reports > 0) {
            foreach (range(1, $reports) as $index) {
                $added['reports'] = random_int(10000000, 99999999);
            }
        }

        $result['added'] = $added;
        return json_encode($result);
    }

    public static function createOrgDeputyshipModel(int $validCount, int $invalidCount)
    {
        $deputyships = [];

        if ($validCount > 0) {
            foreach (range(1, $validCount) as $index) {
                $deputyships[] = (new OrgDeputyshipDto())->setIsValid(true);
            }
        }

        if ($invalidCount > 0) {
            foreach (range(1, $invalidCount) as $index) {
                $deputyships[] = (new OrgDeputyshipDto())->setIsValid(false);
            }
        }

        return $deputyships;
    }

    private static function generateValidOrgDeputyshipArray()
    {
        $faker = Factory::create();

        return [
            'Email'        => $faker->safeEmail,
            'Deputy No'    => (string) $faker->randomNumber(8),
            'Dep Postcode' => Address::postcode(),
            'Dep Forename' => $faker->firstName,
            'Dep Surname'  => $faker->lastName,
            'Dep Type'     => (string) $faker->randomElement([21,22,23,24,25,26,27,29,50,63]),
            'Dep Adrs1'    => $faker->buildingNumber . ' ' . $faker->streetName,
            'Dep Adrs2'    => Address::cityPrefix() . ' ' . $faker->city,
            'Dep Adrs3'    => $faker->city,
            'Dep Adrs4'    => Address::county(),
            'Dep Adrs5'    => 'UK',
            'Case'       => (string) $faker->randomNumber(8),
            'Forename'   => $faker->firstName,
            'Surname'    => $faker->lastName,
            'Corref'     => 'A3',
            'Report Due' => $faker->dateTimeThisYear->format('d-M-Y'),
        ];
    }

    private static function generateInvalidOrgDeputyshipArray()
    {
        $invalid = self::generateValidOrgDeputyshipArray();
        $invalid['Email'] = '';

        return $invalid;
    }

    public static function namedDeputyWasCreated(OrgDeputyshipDto $orgDeputyship, NamedDeputyRepository $namedDeputyRepository)
    {
        return $namedDeputyRepository->findOneBy(['email1' => $orgDeputyship->getEmail()]) instanceof NamedDeputy;
    }

    public static function organisationWasCreated(string $emailIdentifier, OrganisationRepository $org)
    {
        return $org->findOneBy(['emailIdentifier' => $emailIdentifier]) instanceof Organisation;
    }
}

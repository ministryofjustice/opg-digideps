<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\TestHelpers;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\DeputyRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ReportRepository;
use App\Service\ReportUtils;
use App\v2\Registration\Assembler\SiriusToOrgDeputyshipDtoAssembler;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Faker\Provider\en_GB\Address;

class OrgDeputyshipDTOTestHelper
{
    public static function generateSiriusOrgDeputyshipCompressedJson(int $validCount, int $invalidCount)
    {
        $deputyships = [];

        if ($validCount > 0) {
            foreach (range(1, $validCount) as $index) {
                $deputyships[] = self::generateValidSiriusOrgDeputyshipArray();
            }
        }

        if ($invalidCount > 0) {
            foreach (range(1, $invalidCount) as $index) {
                $deputyships[] = self::generateInvalidOrgDeputyshipArray();
            }
        }

        return base64_encode(gzcompress(json_encode($deputyships), 9));
    }

    /**
     * @return array
     */
    public static function generateValidSiriusOrgDeputyshipArray()
    {
        $faker = Factory::create();
        $courtOrderMadeDate = \DateTimeImmutable::createFromMutable($faker->dateTimeThisYear());
        $reportPeriodEndDate = $courtOrderMadeDate->modify('12 months - 1 day');

        return [
            'Case' => (string) $faker->randomNumber(8),
            'ClientForename' => $faker->firstName(),
            'ClientSurname' => $faker->lastName(),
            'ClientDateOfBirth' => $faker->dateTime()->format('Y-m-d'),
            'ClientAddress1' => $faker->buildingNumber().' '.$faker->streetName(),
            'ClientAddress2' => Address::cityPrefix().' '.$faker->city(),
            'ClientAddress3' => Address::county(),
            'ClientAddress4' => null,
            'ClientAddress5' => null,
            'ClientPostcode' => Address::postcode(),
            'DeputyUid' => (string) $faker->randomNumber(8),
            'DeputyType' => $faker->randomElement(['PRO', 'PA']),
            'DeputyEmail' => sprintf('%s@%s%s.com', $faker->userName(), $faker->randomNumber(8), $faker->domainWord()),
            'DeputyOrganisation' => $faker->company(),
            'DeputyForename' => $faker->firstName(),
            'DeputySurname' => $faker->lastName(),
            'DeputyAddress1' => $faker->streetName(),
            'DeputyAddress2' => Address::cityPrefix().' '.$faker->city(),
            'DeputyAddress3' => $faker->city(),
            'DeputyAddress4' => Address::county(),
            'DeputyAddress5' => 'UK',
            'DeputyPostcode' => Address::postcode(),
            'MadeDate' => $courtOrderMadeDate->format('Y-m-d'),
            'LastReportDay' => $reportPeriodEndDate->format('Y-m-d'),
            'ReportType' => $faker->randomElement(['OPG102', 'OPG103', 'OPG104']),
            'OrderType' => $faker->randomElement(['pfa', 'hw']),
            'Hybrid' => 'SINGLE',
        ];
    }

    private static function generateInvalidOrgDeputyshipArray()
    {
        $invalid = self::generateValidSiriusOrgDeputyshipArray();
        $invalid['DeputyEmail'] = '';

        return $invalid;
    }

    /**
     * @return OrgDeputyshipDto[]
     */
    public static function generateSiriusOrgDeputyshipDtos(int $validCount, int $invalidCount)
    {
        $json = self::generateSiriusOrgDeputyshipDecompressedJson($validCount, $invalidCount);
        $dtos = [];
        $reportUtils = new ReportUtils();
        $assembler = new SiriusToOrgDeputyshipDtoAssembler($reportUtils);

        foreach (json_decode($json, true) as $dtoArray) {
            $dtos[] = $assembler->assembleSingleDtoFromArray($dtoArray);
        }

        return $dtos;
    }

    public static function generateSiriusOrgDeputyshipDecompressedJson(int $validCount, int $invalidCount)
    {
        $deputyships = [];

        if ($validCount > 0) {
            foreach (range(1, $validCount) as $index) {
                $deputyships[] = self::generateValidSiriusOrgDeputyshipArray();
            }
        }

        if ($invalidCount > 0) {
            foreach (range(1, $invalidCount) as $index) {
                $deputyships[] = self::generateInvalidOrgDeputyshipArray();
            }
        }

        return json_encode($deputyships);
    }

    public static function deputyWasCreated(OrgDeputyshipDto $orgDeputyship, DeputyRepository $deputyRepository)
    {
        return $deputyRepository->findOneBy(['deputyUid' => $orgDeputyship->getDeputyUid()]) instanceof Deputy;
    }

    public static function organisationWasCreated(string $emailIdentifier, OrganisationRepository $orgRepo)
    {
        return $orgRepo->findOneBy(['emailIdentifier' => $emailIdentifier]) instanceof Organisation;
    }

    public static function clientWasCreated(OrgDeputyshipDto $orgDeputyship, ClientRepository $clientRepo)
    {
        return $clientRepo->findByCaseNumber($orgDeputyship->getCaseNumber()) instanceof Client;
    }

    public static function clientAndOrgAreAssociated(OrgDeputyshipDto $orgDeputyship, ClientRepository $clientRepo, OrganisationRepository $orgRepo)
    {
        $client = $clientRepo->findByCaseNumber($orgDeputyship->getCaseNumber());

        $orgEmailIdentifier = explode('@', $orgDeputyship->getDeputyEmail())[1];
        $org = $orgRepo->findOneBy(['emailIdentifier' => $orgEmailIdentifier]);

        return $org->getClients()->contains($client) && $client->getOrganisation() === $org;
    }

    public static function clientAndDeputyAreAssociated(OrgDeputyshipDto $orgDeputyship, ClientRepository $clientRepo, DeputyRepository $deputyRepo)
    {
        $client = $clientRepo->findByCaseNumber($orgDeputyship->getCaseNumber());
        $deputy = $deputyRepo->findOneBy(['deputyUid' => $orgDeputyship->getDeputyUid()]);

        return $client->getDeputy() === $deputy;
    }

    public static function clientAndDeputyAreNotAssociated(OrgDeputyshipDto $orgDeputyship, ClientRepository $clientRepo, DeputyRepository $deputyRepo)
    {
        $client = $clientRepo->findByCaseNumber($orgDeputyship->getCaseNumber());
        $deputy = $deputyRepo->findOneBy(['email1' => $orgDeputyship->getDeputyEmail()]);

        return !($client->getDeputy() === $deputy);
    }

    public static function clientHasAReportOfType(string $caseNumber, string $reportType, ClientRepository $clientRepo)
    {
        $client = $clientRepo->findByCaseNumber($caseNumber);

        return $client->getReports()->first()->getType() == $reportType;
    }

    public static function reportTypeHasChanged(string $oldReportType, Client $client, ReportRepository $reportRepo)
    {
        $report = $reportRepo->findOneBy(['client' => $client]);

        return $report->getType() !== $oldReportType;
    }

    /**
     * @return Deputy
     */
    public static function ensureDeputyInUploadExists(OrgDeputyshipDto $dto, EntityManager $em)
    {
        $deputy = (new Deputy())
            ->setEmail1($dto->getDeputyEmail())
            ->setDeputyUid($dto->getDeputyUid())
            ->setFirstname($dto->getDeputyFirstname())
            ->setLastname($dto->getDeputyLastname())
            ->setAddress1($dto->getDeputyAddress1())
            ->setAddress2($dto->getDeputyAddress2())
            ->setAddress3($dto->getDeputyAddress3())
            ->setAddress4($dto->getDeputyAddress4())
            ->setAddress5($dto->getDeputyAddress5())
            ->setAddressPostcode($dto->getDeputyPostcode());

        $em->persist($deputy);
        $em->flush();

        return $deputy;
    }

    /**
     * @return Organisation
     */
    public static function ensureOrgInUploadExists(string $orgIdentifier, EntityManager $em)
    {
        $organisation = (new Organisation())
            ->setName('Your Organisation')
            ->setEmailIdentifier($orgIdentifier)
            ->setIsActivated(false);

        $em->persist($organisation);
        $em->flush();

        return $organisation;
    }

    public static function ensureClientInUploadExists(OrgDeputyshipDto $dto, EntityManager $em)
    {
        $client = (new Client())
            ->setCaseNumber($dto->getCaseNumber())
            ->setFirstname($dto->getClientFirstname())
            ->setLastname($dto->getClientLastname())
            ->setCourtDate($dto->getCourtDate());

        $em->persist($client);
        $em->flush();

        return $client;
    }

    public static function ensureClientInUploadExistsAndHasALayDeputy(OrgDeputyshipDto $dto, EntityManager $em)
    {
        $faker = Factory::create();

        $layDeputy = (new User())
            ->setRoleName(User::ROLE_LAY_DEPUTY)
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setEmail($faker->email())
            ->setCoDeputyClientConfirmed(false);

        $client = (new Client())
            ->setCaseNumber($dto->getCaseNumber())
            ->setFirstname($dto->getClientFirstname())
            ->setLastname($dto->getClientLastname())
            ->setCourtDate(new \DateTime())
            ->addUser($layDeputy);

        $em->persist($layDeputy);
        $em->persist($client);
        $em->flush();

        return $client;
    }

    public static function ensureAReportExistsAndIsAssociatedWithClient(
        Client $client,
        EntityManager $em,
        string $reportType = '103-5',
        string $startDate = '2019-11-01',
        string $endDate = '2020-10-31',
    ) {
        $report = new Report($client, $reportType, new \DateTime($startDate), new \DateTime($endDate));
        $client->addReport($report);

        $em->persist($report);
        $em->persist($client);
        $em->flush();

        return $report;
    }
}

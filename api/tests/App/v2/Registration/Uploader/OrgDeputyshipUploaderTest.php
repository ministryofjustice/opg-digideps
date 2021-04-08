<?php declare(strict_types=1);

namespace Tests\App\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Repository\ClientRepository;
use App\Repository\NamedDeputyRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ReportRepository;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use App\v2\Registration\Uploader\OrgDeputyshipUploader;
use DateTime;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\App\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;

class OrgDeputyshipUploaderTest extends KernelTestCase
{
    /** @var OrgDeputyshipUploader */
    private $sut;

    /** @var EntityManager */
    private $em;

    /** @var NamedDeputyRepository */
    private $namedDeputyRepository;

    /** @var OrganisationRepository */
    private $orgRepository;

    /** @var ClientRepository */
    private $clientRepository;

    /** @var ReportRepository */
    private $reportRepository;

    public function setUp(): void
    {
        self::bootKernel(['environment' => 'test', 'debug' => false]);

        $container = self::$container;
        $this->em = $container
            ->get('doctrine')
            ->getManager();

        $this->namedDeputyRepository = $this->em->getRepository(NamedDeputy::class);
        $this->orgRepository = $this->em->getRepository(Organisation::class);
        $this->clientRepository = $this->em->getRepository(Client::class);
        $this->reportRepository = $this->em->getRepository(Report::class);

        $orgFactory = $container->get('App\Factory\OrganisationFactory');
        $clientAssembler = $container->get('App\v2\Assembler\ClientAssembler');
        $namedDeputyAssembler = $container->get('App\v2\Assembler\NamedDeputyAssembler');

        $this->sut = new OrgDeputyshipUploader($this->em, $orgFactory, $clientAssembler, $namedDeputyAssembler);

        $this->purgeDatabase();
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    /** @test  */
    public function upload_new_named_deputies_are_created()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::namedDeputyWasCreated($deputyships[0], $this->namedDeputyRepository),
            sprintf('Named deputy with email %s could not be found', $deputyships[0]->getDeputyEmail())
        );
    }

    /** @test */
    public function upload_existing_named_deputies_are_not_processed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);
        OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['named_deputies']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function upload_named_deputy_with_partial_details_match_creates_new_deputy()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);
        $namedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $namedDeputy->setFirstname('Notmatch');

        $this->em->persist($namedDeputy);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(1, $actualUploadResults['added']['named_deputies']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function upload_new_organisations_are_created()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        $domainArray = explode('@', $deputyships[0]->getDeputyEmail());

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::organisationWasCreated($domainArray[1], $this->orgRepository),
            sprintf('Organisation with email identifier %s could not be found', $domainArray[1])
        );
    }

    /** @test */
    public function upload_existing_organisations_are_not_processed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];
        OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, $this->em);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['organisations']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function upload_new_clients_are_created()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientWasCreated($deputyships[0], $this->clientRepository),
            sprintf('Client with case number %s could not be found', $deputyships[0]->getCaseNumber())
        );
    }

    /**
     * @dataProvider existingClientProvider
     * @test
     */
    public function upload_existing_clients_made_date_is_updated(DateTime $existingCourtDate, DateTime $uploadCourtDate)
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);
        $deputyships[0]->setCourtDate($uploadCourtDate);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $client->setCourtDate($existingCourtDate);

        $this->em->persist($client);
        $this->em->flush();

        $this->sut->upload($deputyships);

        $client = $this->clientRepository->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);

        self::assertEquals($uploadCourtDate, $client->getCourtDate());
    }

    public function existingClientProvider()
    {
        return [
            'Same court date' => [new DateTime('Today'), new DateTime('Today')],
            'Updated court date' => [new DateTime('Today'), new DateTime('Tomorrow')]
        ];
    }

    /** @test  */
    public function upload_client_and_org_are_associated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndOrgAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->orgRepository
            ),
            sprintf(
                'Client with case number "%s" and Organisation with identifier "%s" were not associated',
                $deputyships[0]->getCaseNumber(),
                $orgIdentifier
            )
        );
    }

    /** @test  */
    public function upload_client_and_named_deputy_are_not_associated_when_client_has_switched_orgs_and_named_deputy_has_changed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $originalNamedDeputy->setEmail1(sprintf('different.deputy@different-domain.com'));

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];
        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, $this->em);
        $organisation->setEmailIdentifier('different-domain.com');

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $client->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);

        $this->em->persist($client);
        $this->em->flush();

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreNotAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with email "%s" are associated when they shouldnt be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyEmail()
            )
        );
    }

    /** @test  */
    public function upload_client_and_named_deputy_are_associated_when_client_has_not_switched_orgs_and_named_deputy_has_changed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $originalNamedDeputy->setEmail1(sprintf('different.deputy@%s', $orgIdentifier));
        $originalNamedDeputy->setDeputyNo('abc123');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, $this->em);
        $organisation->setEmailIdentifier($orgIdentifier);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $client->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);

        $this->em->persist($client);
        $this->em->flush();

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with email "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyEmail()
            )
        );
    }

    /** @test */
    public function upload_reports_are_created_for_new_clients()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        $caseNumber = $deputyships[0]->getCaseNumber();
        $reportType = $deputyships[0]->getReportType();

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::ClientHasAReportOfType($caseNumber, $reportType, $this->clientRepository),
            sprintf('Client with case number "%s" did not have an associated report of type %s', $caseNumber, $reportType)
        );
    }

    /** @test */
    public function upload_existing_report_type_is_changed_if_type_is_different()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);
        $changedReportType = '102-5';
        $deputyships[0]->setReportType($changedReportType);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $oldReportType = (OrgDeputyshipDTOTestHelper::ensureAReportExistsAndIsAssociatedWithClient($client, $this->em))->getType();

        $this->sut->upload($deputyships);

        $caseNumber = $deputyships[0]->getCaseNumber();

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::reportTypeHasChanged($oldReportType, $client, $this->reportRepository),
            sprintf(
                'Report associated to Client with case number "%s" had report type %s after upload which is the same as %s',
                $caseNumber,
                $client->getReports()->first()->getType(),
                $oldReportType
            )
        );
    }

    /**
     * @test
     *@dataProvider errorProvider
     */
    public function upload_errors_are_added_to_error_array(OrgDeputyshipDto $dto, array $expectedErrorStrings)
    {
        $uploadResults = $this->sut->upload([$dto]);

        foreach ($expectedErrorStrings as $expectedErrorString) {
            foreach ($uploadResults['errors'] as $actualError) {
                self::assertStringContainsString(
                    $expectedErrorString,
                    $actualError,
                    sprintf('Expected error string "%s" was not in the errors array', $expectedErrorString)
                );
            }
        }
    }

    public function errorProvider()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        return [
            'Missing deputy email' => [(clone($deputyships[0]))->setDeputyEmail(null), ['Deputy Email']],
            'Missing start date' => [(clone($deputyships[0]))->setReportStartDate(null), ['Report Start Date']],
            'Missing end date' => [(clone($deputyships[0]))->setReportEndDate(null), ['Report End Date']],
            'Missing court date' => [(clone($deputyships[0]))->setCourtDate(null), ['Court Date']],
            'All missing' => [
                (clone($deputyships[0]))->setDeputyEmail(null)->setReportStartDate(null)->setReportEndDate(null)->setCourtDate(null),
                ['Report Start Date', 'Report End Date', 'Court Date', 'Deputy Email']
            ],
        ];
    }

    /** @test  */
    public function upload_existing_clients_with_lay_deputies_throws_an_error()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);
        OrgDeputyshipDTOTestHelper::ensureClientInUploadExistsAndHasALayDeputy($deputyships[0], $this->em);

        $uploadResults = $this->sut->upload($deputyships);

        $errorMessage = sprintf('Error for case "%s": case number already used', $deputyships[0]->getCaseNumber());

        self::assertTrue(
            in_array($errorMessage, $uploadResults['errors']),
            sprintf('Expected error message "%s" was not in the errors array', $errorMessage)
        );
    }

    /** @test */
    public function upload_uploading_the_same_dto_a_second_time_does_not_create_duplicates()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(1, 0);

        $firstUploadResults = $this->sut->upload($deputyships);

        foreach ($firstUploadResults['added'] as $result) {
            self::assertCount(
                1,
                $result,
                sprintf('Expecting 1, got %d', count($result))
            );
        }

        $secondUploadResult = $this->sut->upload($deputyships);

        foreach ($secondUploadResult['added'] as $result) {
            self::assertCount(
                0,
                $result,
                sprintf('Expecting 0, got %d', count($result))
            );
        }
    }
}

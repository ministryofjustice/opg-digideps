<?php declare(strict_types=1);

namespace Tests\AppBundle\v2\Registration\Uploader;

use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\Repository\NamedDeputyRepository;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\v2\Registration\DTO\OrgDeputyshipDto;
use AppBundle\v2\Registration\Uploader\OrgDeputyshipUploader;
use DateTime;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\AppBundle\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;

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

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->em = $container->get('em');
        $this->namedDeputyRepository = $this->em->getRepository(NamedDeputy::class);
        $this->orgRepository = $this->em->getRepository(Organisation::class);
        $this->clientRepository = $this->em->getRepository(Client::class);

        $orgFactory = $container->get('AppBundle\Factory\OrganisationFactory');

        $this->sut = new OrgDeputyshipUploader($this->em, $orgFactory);

        $this->purgeDatabase();
    }

    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->em);
        $purger->purge();
    }

    /**
     * @test
     * @dataProvider uploadProvider
     */
    public function upload_provides_feedback_on_entities_processed(
        array $deputyships,
        int $expectedClients,
        int $expectedDischargedClients,
        int $expectedNamedDeputies,
        int $expectedReports,
        int $expectedOrganisations,
        int $expectedErrors
    ) {
        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount($expectedClients, $actualUploadResults['added']['clients']);
        self::assertCount($expectedDischargedClients, $actualUploadResults['added']['discharged_clients']);
        self::assertCount($expectedNamedDeputies, $actualUploadResults['added']['named_deputies']);
        self::assertCount($expectedReports, $actualUploadResults['added']['reports']);
        self::assertCount($expectedOrganisations, $actualUploadResults['added']['organisations']);
        self::assertEquals($expectedErrors, $actualUploadResults['errors']);
    }

    // add extra field in array for orgs created
    public function uploadProvider()
    {
        return [
            '3 valid Org Deputyships' =>
                [
                    OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(3, 0), 3, 0, 3, 0, 3, 0
                ],
            '2 valid, 1 invalid Org Deputyships' =>
                [
                    OrgDeputyshipDTOTestHelper::generateOrgDeputyshipDtos(2, 1), 2, 0, 2, 0, 2, 1
                ]
        ];
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
        self::assertEquals(0, $actualUploadResults['errors']);
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
        self::assertEquals(0, $actualUploadResults['errors']);
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
        OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($deputyships[0], $this->em);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['organisations']);
        self::assertEquals(0, $actualUploadResults['errors']);
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

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($deputyships[0], $this->em);
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
        $originalNamedDeputy ->setEmail1(sprintf('different.deputy@%s', $orgIdentifier));

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($deputyships[0], $this->em);
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

    // Handle existing case numbers - add error

    // Make sure 0s are respected in DTO object for client case number

    // Make sure first and last names are trimmed for client name
}

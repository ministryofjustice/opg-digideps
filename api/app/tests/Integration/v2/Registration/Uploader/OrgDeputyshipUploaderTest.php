<?php

declare(strict_types=1);

namespace App\Tests\Integration\v2\Registration\Uploader;

use App\Tests\Integration\ApiTestTrait;
use DateTime;
use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Factory\OrganisationFactory;
use App\Repository\ClientRepository;
use App\Repository\DeputyRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ReportRepository;
use App\Tests\Integration\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\DeputyAssembler;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use App\v2\Registration\Uploader\OrgDeputyshipUploader;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OrgDeputyshipUploaderTest extends KernelTestCase
{
    use ApiTestTrait;

    private DeputyRepository $deputyRepository;
    private OrganisationRepository $orgRepository;
    private ClientRepository $clientRepository;
    private ReportRepository $reportRepository;
    private OrgDeputyshipUploader $sut;

    public function setUp(): void
    {
        parent::setUp();

        self::configureTest();

        /** @var DeputyRepository $deputyRepository */
        $deputyRepository = self::$entityManager->getRepository(Deputy::class);
        $this->deputyRepository = $deputyRepository;

        /** @var OrganisationRepository $orgRepository */
        $orgRepository = self::$entityManager->getRepository(Organisation::class);
        $this->orgRepository = $orgRepository;

        /** @var ClientRepository $clientRepository */
        $clientRepository = self::$entityManager->getRepository(Client::class);
        $this->clientRepository = $clientRepository;

        /** @var ReportRepository $reportRepository */
        $reportRepository = self::$entityManager->getRepository(Report::class);
        $this->reportRepository = $reportRepository;

        $logger = $this->createMock(LoggerInterface::class);

        $orgFactory = self::$container->get(OrganisationFactory::class);
        $clientAssembler = self::$container->get(ClientAssembler::class);
        $deputyAssembler = self::$container->get(DeputyAssembler::class);

        $this->sut = new OrgDeputyshipUploader(self::$entityManager, $orgFactory, $clientAssembler, $deputyAssembler, $logger);

        $this->purgeDatabase();
    }

    public function testUploadNewDeputiesAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::deputyWasCreated($deputyships[0], $this->deputyRepository),
            sprintf('Deputy with DeputyUid %s could not be found', $deputyships[0]->getDeputyUid())
        );
    }

    public function testUploadExistingDeputiesAreNotProcessed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['deputies']);
        self::assertCount(0, $actualUploadResults['updated']['deputies']);
        self::assertTrue(empty($actualUploadResults['errors']['messages']));
    }

    public function testUploadDeputyWithSameDetailsButNewDeputyUidCreatesNewDeputy()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $deputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $deputy->setDeputyUid('12345678');

        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(1, $actualUploadResults['added']['deputies']);
        self::assertCount(0, $actualUploadResults['updated']['deputies']);
        self::assertTrue(empty($actualUploadResults['errors']['messages']));
    }

    public function testUploadExistingDeputiesWithNewAddressDetailsAreUpdated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $deputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $deputy->setAddress1('10 New Road');

        self::$entityManager->persist($deputy);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['deputies']);
        self::assertCount(1, $actualUploadResults['updated']['deputies']);
        self::assertTrue(empty($actualUploadResults['errors']['messages']));
    }

    public function testUploadNewOrganisationsAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        $domainArray = explode('@', $deputyships[0]->getDeputyEmail());

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::organisationWasCreated($domainArray[1], $this->orgRepository),
            sprintf('Organisation with email identifier %s could not be found', $domainArray[1])
        );
    }

    public function testUploadExistingOrganisationsAreNotProcessed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];
        OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, self::$entityManager);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['organisations']);
        self::assertTrue(empty($actualUploadResults['errors']['messages']));
    }

    public function testUploadNewClientsAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientWasCreated($deputyships[0], $this->clientRepository),
            sprintf('Client with case number %s could not be found', $deputyships[0]->getCaseNumber())
        );
    }

    public function testUploadClientAndOrgAreAssociated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

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

    // Temporary test to ensure that we are not updating the client if court order made date has changed
    public function testUploadClientAndDeputyAreNotAssociatedWhenClientHasNewCourtOrderMadeDate()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $originalOrgIdentifier = 'differentorg.co.uk';

        $originalDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $originalDeputy->setEmail1(sprintf('different.deputy@%s', $originalOrgIdentifier));
        $originalDeputy->setDeputyUid('ABCD1234');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($originalOrgIdentifier, self::$entityManager);
        $organisation->setEmailIdentifier($originalOrgIdentifier);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $client->setDeputy($originalDeputy)->setOrganisation($organisation);
        $client->setCourtDate(new DateTime());

        self::$entityManager->persist($client);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertFalse(
            OrgDeputyshipDTOTestHelper::clientAndDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->deputyRepository
            ),
            sprintf(
                'Client with case number "%s" and deputy with uid "%s" are associated when they should not be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(0, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = self::$entityManager->getRepository(Client::class)->find($client);
        self::$entityManager->refresh($updatedClient);

        self::assertEquals($originalDeputy->getDeputyUid(), $updatedClient->getDeputy()->getDeputyUid());
    }

    public function testUploadClientAndDeputyAreAssociatedWhenClientHasSwitchedOrgsAndDeputyHasNotChangedAndMadeDateHasNotChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $originalOrgIdentifier = 'differentorg.co.uk';

        $originalDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($originalOrgIdentifier, self::$entityManager);
        $organisation->setEmailIdentifier($originalOrgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $originalClient->setDeputy($originalDeputy)->setOrganisation($organisation);

        self::$entityManager->persist($originalClient);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->deputyRepository
            ),
            sprintf(
                'Client with case number "%s" and deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(1, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = self::$entityManager->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        self::$entityManager->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($originalDeputy->getDeputyUid(), $updatedClient->getDeputy()->getDeputyUid());

        $newOrgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];
        self::assertEquals($newOrgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    public function testUploadClientAndDeputyAreNotAssociatedWhenClientHasSwitchedOrgsAndDeputyHasNotChangedAndMadeDateHasChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $originalOrgIdentifier = 'differentorg.co.uk';

        $originalDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($originalOrgIdentifier, self::$entityManager);
        $organisation->setEmailIdentifier($originalOrgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $originalClient->setDeputy($originalDeputy)->setOrganisation($organisation);
        $originalClient->setCourtDate(new DateTime());

        self::$entityManager->persist($originalClient);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->deputyRepository
            ),
            sprintf(
                'Client with case number "%s" and deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients'], 'Unexpected clients have been added to the Database');
        self::assertCount(0, $actualUploadResults['updated']['clients'], 'Unexpected clients have been updated within the Database');

        /** @var Client $updatedClient */
        $updatedClient = self::$entityManager->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        self::$entityManager->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($originalDeputy->getDeputyUid(), $updatedClient->getDeputy()->getDeputyUid());

        self::assertEquals($originalOrgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    public function testUploadClientAndDeputyAreAssociatedWhenClientHasNotSwitchedOrgsAndDeputyHasChangedAndMadeDateHasChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];

        $originalDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $originalDeputy->setEmail1(sprintf('different.deputy@%s', $orgIdentifier));
        $originalDeputy->setDeputyUid('ABCD1234');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, self::$entityManager);
        $organisation->setEmailIdentifier($orgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $originalClient->setDeputy($originalDeputy)->setOrganisation($organisation);
        $originalClient->setCourtDate(new DateTime());

        self::$entityManager->persist($originalClient);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->deputyRepository
            ),
            sprintf(
                'Client with case number "%s" and deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(1, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = self::$entityManager->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        self::$entityManager->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($deputyships[0]->getDeputyUid(), $updatedClient->getDeputy()->getDeputyUid());

        self::assertEquals($orgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    public function testUploadClientAndDeputyAreAssociatedWhenClientHasNotSwitchedOrgsAndDeputyHasChangedAndMadeDateNotChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];

        $originalDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $originalDeputy->setEmail1(sprintf('different.deputy@%s', $orgIdentifier));
        $originalDeputy->setDeputyUid('ABCD1234');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, self::$entityManager);
        $organisation->setEmailIdentifier($orgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $originalClient->setDeputy($originalDeputy)->setOrganisation($organisation);

        self::$entityManager->persist($originalClient);
        self::$entityManager->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->deputyRepository
            ),
            sprintf(
                'Client with case number "%s" and deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(1, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = self::$entityManager->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        self::$entityManager->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($deputyships[0]->getDeputyUid(), $updatedClient->getDeputy()->getDeputyUid());

        self::assertEquals($orgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    public function testUploadReportsAreCreatedForNewClients()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        $caseNumber = $deputyships[0]->getCaseNumber();
        $reportType = $deputyships[0]->getReportType();

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::ClientHasAReportOfType($caseNumber, $reportType, $this->clientRepository),
            sprintf('Client with case number "%s" did not have an associated report of type %s', $caseNumber, $reportType)
        );
    }

    public function testUploadExistingReportTypeIsChangedIfTypeIsDifferent()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $changedReportType = '102-5';
        $deputyships[0]->setReportType($changedReportType);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $oldReportType = OrgDeputyshipDTOTestHelper::ensureAReportExistsAndIsAssociatedWithClient($client, self::$entityManager)->getType();

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

    public function testUploadExistingReportTypeIsChangedIfTypeIsDifferentAndDeputyUidMatchesForDualCase()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $changedReportType = '102-5';
        $deputyships[0]->setReportType($changedReportType);
        $deputyships[0]->setHybrid(OrgDeputyshipDto::DUAL_TYPE);
        $deputyships[0]->setDeputyUid('12345678');

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $deputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $client->setDeputy($deputy);
        $oldReportType = OrgDeputyshipDTOTestHelper::ensureAReportExistsAndIsAssociatedWithClient($client, self::$entityManager)->getType();

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

    public function testUploadExistingReportTypeIsNotChangedIfTypeIsDifferentAndDeputyUidDoesNotMatchForDualCase()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $changedReportType = '102-5';
        $deputyships[0]->setReportType($changedReportType);
        $deputyships[0]->setHybrid(OrgDeputyshipDto::DUAL_TYPE);
        $deputyships[0]->setDeputyUid('87654321');

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $deputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);
        $deputy->setDeputyUid('12345678');
        $client->setDeputy($deputy);

        $oldReportType = OrgDeputyshipDTOTestHelper::ensureAReportExistsAndIsAssociatedWithClient($client, self::$entityManager)->getType();

        $this->sut->upload($deputyships);

        $caseNumber = $deputyships[0]->getCaseNumber();

        self::assertFalse(
            OrgDeputyshipDTOTestHelper::reportTypeHasChanged($oldReportType, $client, $this->reportRepository),
            sprintf(
                'Report associated to Client with case number "%s" had report type %s after upload which is not the same as %s',
                $caseNumber,
                $client->getReports()->first()->getType(),
                $oldReportType
            )
        );
    }

    public static function errorProvider(): array
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        return [
            'Missing deputy email' => [(clone $deputyships[0])->setDeputyEmail(''), ['DeputyEmail']],
            'Missing end date' => [(clone $deputyships[0])->setReportEndDate(null), ['LastReportDay']],
            'Missing court date' => [(clone $deputyships[0])->setCourtDate(null), ['MadeDate']],
            'All missing' => [
                (clone $deputyships[0])->setDeputyEmail('')->setReportStartDate(null)->setReportEndDate(null)->setCourtDate(null),
                ['DeputyEmail', 'LastReportDay', 'MadeDate'],
            ],
        ];
    }

    /**
     * @dataProvider errorProvider
     */
    public function testUploadErrorsAreAddedToErrorArray(OrgDeputyshipDto $dto, array $expectedErrorStrings)
    {
        $uploadResults = $this->sut->upload([$dto]);

        foreach ($expectedErrorStrings as $expectedErrorString) {
            foreach ($uploadResults['errors']['messages'] as $actualError) {
                self::assertStringContainsString(
                    $expectedErrorString,
                    $actualError,
                    sprintf('Expected error string "%s" was not in the errors array', $expectedErrorString)
                );
            }
        }
    }

    public function testUploadExistingClientsWithLayDeputiesThrowsAnError()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        OrgDeputyshipDTOTestHelper::ensureClientInUploadExistsAndHasALayDeputy($deputyships[0], self::$entityManager);

        $uploadResults = $this->sut->upload($deputyships);

        $errorMessage = sprintf('Error for case %s: case number already used', $deputyships[0]->getCaseNumber());

        self::assertTrue(
            in_array($errorMessage, $uploadResults['errors']['messages']),
            sprintf('Expected error message "%s" was not in the errors array', $errorMessage)
        );
    }

    public function testUploadUploadingTheSameDtoASecondTimeDoesNotCreateDuplicates()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

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

    public function testUploadExistingClientsWithMissingCourtDateHaveCourtDateAdded()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $client->setCourtDate(null);

        self::$entityManager->persist($client);
        self::$entityManager->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        $updatedClient = self::$entityManager->getRepository(Client::class)->find($client);

        self::assertEquals(
            $deputyships[0]->getCourtDate(),
            $updatedClient->getCourtDate()
        );
    }

    public function testUploadRowsWithArchivedClientsAreSkipped()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $deputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);

        $client->setDeputy($deputy);
        $client->setArchivedAt(new DateTime());

        self::$entityManager->persist($client);
        self::$entityManager->flush();

        $deputyships[0]->setDeputyAddress1('New Address');
        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            0,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 0, got %d', count($uploadResults['updated']['clients']))
        );

        self::assertCount(
            0,
            $uploadResults['errors']['messages'],
            sprintf('Expecting 0, got %d', count($uploadResults['errors']['messages']))
        );

        self::assertEquals(
            1,
            $uploadResults['skipped'],
            sprintf('Expecting 1, got %d', $uploadResults['skipped'])
        );

        $updatedClient = self::$entityManager->getRepository(Client::class)->find($client);

        self::assertNotEquals(
            $deputyships[0]->getDeputyAddress1(),
            $updatedClient->getDeputy()->getAddress1()
        );
    }

    public function testUploadCaseNumberSearchIsNotCaseSensitive()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $client->setCourtDate(null);
        $client->setCaseNumber('1234567t');
        $deputyships[0]->setCaseNumber('1234567T');

        self::$entityManager->persist($client);
        self::$entityManager->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        $updatedClient = self::$entityManager->getRepository(Client::class)->find($client);

        self::assertEquals(
            $deputyships[0]->getCourtDate(),
            $updatedClient->getCourtDate()
        );
    }

    public function testUploadOnlyUpdateDeputyNameAndAddressIfDTODeputyUidMatchesExistingDeputyUid()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $existingDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);

        $deputyships[0]->setDeputyUid('abc123');
        $deputyships[0]->setDeputyFirstname('Bob');
        $deputyships[0]->setDeputyLastname('Smith');
        $deputyships[0]->setClientAddress1('1 Fakeville Avenue');

        $existingDeputy->setDeputyUid('xyz789')
            ->setFirstname('Joe')
            ->setLastname('Joson')
            ->setAddress1('10 PretendVille Road');

        $client->setDeputy($existingDeputy);

        self::$entityManager->persist($client);
        self::$entityManager->persist($existingDeputy);
        self::$entityManager->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        /** @var Deputy $updatedDeputy */
        $updatedDeputy = self::$entityManager->getRepository(Deputy::class)->find($existingDeputy);
        self::$entityManager->refresh($updatedDeputy);

        self::assertEquals('Joe', $updatedDeputy->getFirstName());
        self::assertEquals('Joson', $updatedDeputy->getLastname());
        self::assertEquals('10 PretendVille Road', $updatedDeputy->getAddress1());
    }

    public function testUploadOnlyUpdateDeputyEmailIfDTODeputyUidMatchesExistingDeputyUid()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], self::$entityManager);
        $existingDeputy = OrgDeputyshipDTOTestHelper::ensureDeputyInUploadExists($deputyships[0], self::$entityManager);

        $deputyships[0]->setDeputyUid('abc123');
        $deputyships[0]->setDeputyEmail('william@somecompany.com');

        $existingDeputy->setDeputyUid('xyz789');
        $existingDeputy->setEmail1('william@differentcompany.com');

        $client->setDeputy($existingDeputy);

        self::$entityManager->persist($client);
        self::$entityManager->persist($existingDeputy);
        self::$entityManager->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        /** @var Deputy $updatedDeputy */
        $updatedDeputy = self::$entityManager->getRepository(Deputy::class)->find($existingDeputy);
        self::$entityManager->refresh($updatedDeputy);

        self::assertEquals('william@differentcompany.com', $updatedDeputy->getEmail1());

        $deputyships[0]->setDeputyUid('xyz789');

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        /** @var Deputy $updatedDeputy */
        $updatedDeputy = self::$entityManager->getRepository(Deputy::class)->find($existingDeputy);
        self::$entityManager->refresh($updatedDeputy);

        self::assertEquals('william@somecompany.com', $updatedDeputy->getEmail1());
    }
}

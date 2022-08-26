<?php

declare(strict_types=1);

namespace App\Tests\Unit\v2\Registration\Uploader;

use App\Entity\Client;
use App\Entity\NamedDeputy;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Repository\ClientRepository;
use App\Repository\NamedDeputyRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ReportRepository;
use App\Tests\Unit\v2\Registration\TestHelpers\OrgDeputyshipDTOTestHelper;
use App\v2\Registration\DTO\OrgDeputyshipDto;
use App\v2\Registration\Uploader\OrgDeputyshipUploader;
use DateTime;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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
    public function uploadNewNamedDeputiesAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::namedDeputyWasCreated($deputyships[0], $this->namedDeputyRepository),
            sprintf('Named deputy with DeputyUid %s could not be found', $deputyships[0]->getDeputyUid())
        );
    }

    /** @test */
    public function uploadExistingNamedDeputiesAreNotProcessed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['named_deputies']);
        self::assertCount(0, $actualUploadResults['updated']['named_deputies']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function uploadNamedDeputyWithSameDetailsButNewDeputyUidCreatesNewDeputy()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $namedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $namedDeputy->setDeputyUid('12345678');

        $this->em->persist($namedDeputy);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(1, $actualUploadResults['added']['named_deputies']);
        self::assertCount(0, $actualUploadResults['updated']['named_deputies']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function uploadExistingNamedDeputiesWithNewAddressDetailsAreUpdated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $namedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $namedDeputy->setAddress1('10 New Road');

        $this->em->persist($namedDeputy);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['named_deputies']);
        self::assertCount(1, $actualUploadResults['updated']['named_deputies']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function uploadNewOrganisationsAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        $domainArray = explode('@', $deputyships[0]->getDeputyEmail());

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::organisationWasCreated($domainArray[1], $this->orgRepository),
            sprintf('Organisation with email identifier %s could not be found', $domainArray[1])
        );
    }

    /** @test */
    public function uploadExistingOrganisationsAreNotProcessed()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];
        OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, $this->em);

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertCount(0, $actualUploadResults['added']['organisations']);
        self::assertTrue(empty($actualUploadResults['errors']));
    }

    /** @test */
    public function uploadNewClientsAreCreated()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientWasCreated($deputyships[0], $this->clientRepository),
            sprintf('Client with case number %s could not be found', $deputyships[0]->getCaseNumber())
        );
    }

//    /**
//     * @dataProvider existingClientProvider
//     * @test
//     */
//    public function uploadExistingClientsWithNewMadeDateCreatesNewReport(DateTime $existingCourtDate, DateTime $uploadCourtDate)
//    {
//        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
//        $deputyships[0]->setCourtDate($uploadCourtDate);
//
//        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
//        $existingReport = OrgDeputyshipDTOTestHelper::ensureAReportExistsAndIsAssociatedWithClient($client, $this->em);
//
//        $client->setCourtDate($existingCourtDate);
//
//        $this->em->persist($client);
//        $this->em->flush();
//
//        $this->sut->upload($deputyships);
//
//        $client = $this->clientRepository->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
//
//        self::assertNotEquals($existingReport->getId(), $client->getCurrentReport()->getId());
//    }
//
//    public function existingClientProvider()
//    {
//        return [
//            'Updated court date' => [new DateTime('Today'), new DateTime('Tomorrow')],
//        ];
//    }

    /** @test */
    public function uploadClientAndOrgAreAssociated()
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
    /** @test  */
    public function uploadClientAndNamedDeputyAreNotAssociatedWhenClientHasNewCourtOrderMadeDate()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $originalOrgIdentifier = 'differentorg.co.uk';

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $originalNamedDeputy->setEmail1(sprintf('different.deputy@%s', $originalOrgIdentifier));
        $originalNamedDeputy->setDeputyUid('ABCD1234');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($originalOrgIdentifier, $this->em);
        $organisation->setEmailIdentifier($originalOrgIdentifier);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $client->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);
        $client->setCourtDate(new DateTime());

        $this->em->persist($client);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertFalse(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with uid "%s" are associated when they should not be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(0, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = $this->em->getRepository(Client::class)->find($client);
        $this->em->refresh($updatedClient);

        self::assertEquals($originalNamedDeputy->getDeputyUid(), $updatedClient->getNamedDeputy()->getDeputyUid());
    }

    /** @test  */
    public function uploadClientAndNamedDeputyAreAssociatedWhenClientHasSwitchedOrgsAndNamedDeputyHasNotChangedAndMadeDateHasNotChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $originalOrgIdentifier = 'differentorg.co.uk';

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($originalOrgIdentifier, $this->em);
        $organisation->setEmailIdentifier($originalOrgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $originalClient->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);

        $this->em->persist($originalClient);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(1, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        $this->em->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($originalNamedDeputy->getDeputyUid(), $updatedClient->getNamedDeputy()->getDeputyUid());

        $newOrgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];
        self::assertEquals($newOrgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    /** @test  */
    public function uploadClientAndNamedDeputyAreNotAssociatedWhenClientHasSwitchedOrgsAndNamedDeputyHasNotChangedAndMadeDateHasChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $originalOrgIdentifier = 'differentorg.co.uk';

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($originalOrgIdentifier, $this->em);
        $organisation->setEmailIdentifier($originalOrgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $originalClient->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);
        $originalClient->setCourtDate(new DateTime());

        $this->em->persist($originalClient);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(0, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        $this->em->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($originalNamedDeputy->getDeputyUid(), $updatedClient->getNamedDeputy()->getDeputyUid());

        self::assertEquals($originalOrgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    /** @test  */
    public function uploadClientAndNamedDeputyAreAssociatedWhenClientHasNotSwitchedOrgsAndNamedDeputyHasChangedAndMadeDateHasChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $originalNamedDeputy->setEmail1(sprintf('different.deputy@%s', $orgIdentifier));
        $originalNamedDeputy->setDeputyUid('ABCD1234');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, $this->em);
        $organisation->setEmailIdentifier($orgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $originalClient->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);
        $originalClient->setCourtDate(new DateTime());

        $this->em->persist($originalClient);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(1, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        $this->em->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($deputyships[0]->getDeputyUid(), $updatedClient->getNamedDeputy()->getDeputyUid());

        self::assertEquals($orgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    /** @test  */
    public function uploadClientAndNamedDeputyAreAssociatedWhenClientHasNotSwitchedOrgsAndNamedDeputyHasChangedAndMadeDateNotChanged()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $orgIdentifier = explode('@', $deputyships[0]->getDeputyEmail())[1];

        $originalNamedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);
        $originalNamedDeputy->setEmail1(sprintf('different.deputy@%s', $orgIdentifier));
        $originalNamedDeputy->setDeputyUid('ABCD1234');

        $organisation = OrgDeputyshipDTOTestHelper::ensureOrgInUploadExists($orgIdentifier, $this->em);
        $organisation->setEmailIdentifier($orgIdentifier);

        $originalClient = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $originalClient->setNamedDeputy($originalNamedDeputy)->setOrganisation($organisation);

        $this->em->persist($originalClient);
        $this->em->flush();

        $actualUploadResults = $this->sut->upload($deputyships);

        self::assertTrue(
            OrgDeputyshipDTOTestHelper::clientAndNamedDeputyAreAssociated(
                $deputyships[0],
                $this->clientRepository,
                $this->namedDeputyRepository
            ),
            sprintf(
                'Client with case number "%s" and named deputy with uid "%s" are not associated when they should be',
                $deputyships[0]->getCaseNumber(),
                $deputyships[0]->getDeputyUid()
            )
        );

        self::assertCount(0, $actualUploadResults['added']['clients']);
        self::assertCount(1, $actualUploadResults['updated']['clients']);

        /** @var Client $updatedClient */
        $updatedClient = $this->em->getRepository(Client::class)->findOneBy(['caseNumber' => $deputyships[0]->getCaseNumber()]);
        $this->em->refresh($updatedClient);

        self::assertEquals($originalClient->getId(), $updatedClient->getId());
        self::assertEquals($deputyships[0]->getDeputyUid(), $updatedClient->getNamedDeputy()->getDeputyUid());

        self::assertEquals($orgIdentifier, $updatedClient->getOrganisation()->getEmailIdentifier());
    }

    /** @test */
    public function uploadReportsAreCreatedForNewClients()
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

    /** @test */
    public function uploadExistingReportTypeIsChangedIfTypeIsDifferent()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $changedReportType = '102-5';
        $deputyships[0]->setReportType($changedReportType);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $oldReportType = OrgDeputyshipDTOTestHelper::ensureAReportExistsAndIsAssociatedWithClient($client, $this->em)->getType();

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
     * @dataProvider errorProvider
     */
    public function uploadErrorsAreAddedToErrorArray(OrgDeputyshipDto $dto, array $expectedErrorStrings)
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
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        return [
            'Missing deputy email' => [(clone $deputyships[0])->setDeputyEmail(''), ['Deputy Email']],
            'Missing start date' => [(clone $deputyships[0])->setReportStartDate(null), ['Report Start Date']],
            'Missing end date' => [(clone $deputyships[0])->setReportEndDate(null), ['Report End Date']],
            'Missing court date' => [(clone $deputyships[0])->setCourtDate(null), ['Court Date']],
            'All missing' => [
                (clone $deputyships[0])->setDeputyEmail('')->setReportStartDate(null)->setReportEndDate(null)->setCourtDate(null),
                ['Report Start Date', 'Report End Date', 'Court Date', 'Deputy Email'],
            ],
        ];
    }

    /** @test  */
    public function uploadExistingClientsWithLayDeputiesThrowsAnError()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        OrgDeputyshipDTOTestHelper::ensureClientInUploadExistsAndHasALayDeputy($deputyships[0], $this->em);

        $uploadResults = $this->sut->upload($deputyships);

        $errorMessage = sprintf('Error for case %s: case number already used', $deputyships[0]->getCaseNumber());

        self::assertTrue(
            in_array($errorMessage, $uploadResults['errors']),
            sprintf('Expected error message "%s" was not in the errors array', $errorMessage)
        );
    }

    /** @test */
    public function uploadUploadingTheSameDtoASecondTimeDoesNotCreateDuplicates()
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

    /** @test */
    public function uploadExistingClientsWithMissingCourtDateHaveCourtDateAdded()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $client->setCourtDate(null);

        $this->em->persist($client);
        $this->em->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        $updatedClient = $this->em->getRepository(Client::class)->find($client);

        self::assertEquals(
            $deputyships[0]->getCourtDate(),
            $updatedClient->getCourtDate()
        );
    }

    /** @test */
    public function uploadRowsWithArchivedClientsAreSkipped()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);

        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $namedDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);

        $client->setNamedDeputy($namedDeputy);
        $client->setArchivedAt(new DateTime());

        $this->em->persist($client);
        $this->em->flush();

        $deputyships[0]->setDeputyAddress1('New Address');
        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            0,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 0, got %d', count($uploadResults['updated']['clients']))
        );

        self::assertCount(
            0,
            $uploadResults['errors'],
            sprintf('Expecting 0, got %d', count($uploadResults['errors']))
        );

        self::assertEquals(
            1,
            $uploadResults['skipped'],
            sprintf('Expecting 1, got %d', $uploadResults['skipped'])
        );

        $updatedClient = $this->em->getRepository(Client::class)->find($client);

        self::assertNotEquals(
            $deputyships[0]->getDeputyAddress1(),
            $updatedClient->getNamedDeputy()->getAddress1()
        );
    }

    /** @test */
    public function uploadCaseNumberSearchIsNotCaseSensitive()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $client->setCourtDate(null);
        $client->setCaseNumber('1234567t');
        $deputyships[0]->setCaseNumber('1234567T');

        $this->em->persist($client);
        $this->em->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        $updatedClient = $this->em->getRepository(Client::class)->find($client);

        self::assertEquals(
            $deputyships[0]->getCourtDate(),
            $updatedClient->getCourtDate()
        );
    }

    /** @test */
    public function uploadOnlyUpdateDeputyNameAndAddressIfDTODeputyUidMatchesExistingDeputyUid()
    {
        $deputyships = OrgDeputyshipDTOTestHelper::generateSiriusOrgDeputyshipDtos(1, 0);
        $client = OrgDeputyshipDTOTestHelper::ensureClientInUploadExists($deputyships[0], $this->em);
        $existingDeputy = OrgDeputyshipDTOTestHelper::ensureNamedDeputyInUploadExists($deputyships[0], $this->em);

        $deputyships[0]->setDeputyUid('abc123');
        $deputyships[0]->setDeputyFirstname('Bob');
        $deputyships[0]->setDeputyLastname('Smith');
        $deputyships[0]->setClientAddress1('1 Fakeville Avenue');

        $existingDeputy->setDeputyUid('xyz789')
            ->setFirstname('Joe')
            ->setLastname('Joson')
            ->setAddress1('10 PretendVille Road');

        $client->setNamedDeputy($existingDeputy);

        $this->em->persist($client);
        $this->em->persist($existingDeputy);
        $this->em->flush();

        $uploadResults = $this->sut->upload($deputyships);

        self::assertCount(
            1,
            $uploadResults['updated']['clients'],
            sprintf('Expecting 1, got %d', count($uploadResults['updated']['clients']))
        );

        /** @var NamedDeputy $updatedNamedDeputy */
        $updatedNamedDeputy = $this->em->getRepository(NamedDeputy::class)->find($existingDeputy);
        $this->em->refresh($updatedNamedDeputy);

        self::assertEquals('Joe', $updatedNamedDeputy->getFirstName());
        self::assertEquals('Joson', $updatedNamedDeputy->getLastname());
        self::assertEquals('10 PretendVille Road', $updatedNamedDeputy->getAddress1());
    }
}

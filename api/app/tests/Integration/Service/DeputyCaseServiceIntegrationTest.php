<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Service\DeputyCaseService;
use App\TestHelpers\ClientTestHelper;
use App\TestHelpers\UserTestHelper;
use App\Tests\Integration\ApiBaseTestCase;
use App\Tests\Integration\Fixtures;
use Doctrine\DBAL\Exception;

class DeputyCaseServiceIntegrationTest extends ApiBaseTestCase
{
    private Fixtures $fixtures;
    private DeputyCaseService $sut;

    public function setUp(): void
    {
        parent::setUp();

        $this->fixtures = new Fixtures($this->entityManager);

        /** @var DeputyCaseService $sut */
        $sut = $this->container->get(DeputyCaseService::class);
        $this->sut = $sut;
    }

    /**
     * @throws Exception
     */
    public function testAddMissingDeputyCaseAssociations(): void
    {
        $caseNumber = '93015923';
        $deputyUid = '89893420';

        // row in pre-reg table associating deputy UID with case number
        $preReg = new PreRegistration(['Case' => $caseNumber, 'DeputyUid' => $deputyUid]);

        $this->entityManager->persist($preReg);

        // client with same case number
        $client = new Client();
        $client->setCaseNumber($caseNumber);

        $this->entityManager->persist($client);

        // dd_user with same deputy UID;
        // note there's no association in deputy_case between client and dd_user
        $user = new User();
        $user->setDeputyUid(intval($deputyUid))
            ->setFirstname('Bill')
            ->setLastname('Sykes')
            ->setPassword('')
            ->setIsPrimary(true)
            ->setActive(true)
            ->setEmail('bill.sykes-deputy-case-candidate@opg.gov.uk');

        $this->entityManager->persist($user);

        $this->entityManager->flush();

        // we should add a deputy_case for this client and dd_user pair
        $numAdded = $this->sut->addMissingDeputyCaseAssociations();

        self::assertEquals(1, $numAdded);

        // check for the association in the db
        $client = $this->entityManager->getRepository(Client::class)->find($client->getId());
        self::assertNotNull($client);

        $associatedUsers = $client->getUsers();
        self::assertCount(1, $associatedUsers);
        self::assertEquals($user->getEmail(), $associatedUsers[0]->getEmail());
    }

    public function testAddMissingDeputyCaseAssociationsCheckPersist(): void
    {
        $associations = [];

        for ($i = 0; $i < 10; ++$i) {
            $caseNumber = "9791592$i";
            $deputyUid = "8589042$i";

            // add pre-reg entries with deputy UID and case number which appear in deputy and case tables respectively
            $this->entityManager->persist(
                $this->fixtures->createPreRegistration($caseNumber, 'OPG102', 'pfa', deputyUid: $deputyUid)
            );

            // add clients with corresponding case numbers
            $client = ClientTestHelper::generateClient($this->entityManager, caseNumber: $caseNumber);
            $this->entityManager->persist($client);

            // add users with corresponding deputy UIDs
            $user = UserTestHelper::createUser(deputyUid: intval($deputyUid));
            $this->entityManager->persist($user);

            $this->entityManager->flush();

            $associations[] = ['clientId' => $client->getId(), 'userId' => $user->getId()];
        }

        // create associations between users (aka the deputy in deputy_case) and clients (aka the case in deputy_case)
        $deputyCasesAdded = $this->sut->addMissingDeputyCaseAssociations(batchSize: 3);

        // check that all the associations have been made
        self::assertEquals(10, $deputyCasesAdded);

        $clientRepo = $this->entityManager->getRepository(Client::class);
        foreach ($associations as $association) {
            /** @var Client $foundClient */
            $foundClient = $clientRepo->find($association['clientId']);
            self::assertNotNull($foundClient);

            /** @var User[] $foundUser */
            foreach ($foundClient->getUsers() as $foundUser) {
                self::assertEquals($association['userId'], $foundUser->getId());
            }
        }
    }
}

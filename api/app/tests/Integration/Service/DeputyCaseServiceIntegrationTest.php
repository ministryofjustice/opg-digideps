<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Client;
use App\Entity\PreRegistration;
use App\Entity\User;
use App\Service\DeputyCaseService;
use App\Tests\Integration\ApiBaseTestCase;
use Doctrine\DBAL\Exception;

class DeputyCaseServiceIntegrationTest extends ApiBaseTestCase
{
    private DeputyCaseService $sut;

    public function setUp(): void
    {
        parent::setUp();

        /** @var DeputyCaseService $sut */
        $sut = $this->container->get(DeputyCaseService::class);
        $this->sut = $sut;
    }

    /**
     * @throws Exception
     */
    public function testCreate(): void
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
}

<?php

namespace DigidepsBehat\v2\Helpers;

use App\Entity\Client;
use App\Entity\Ndr\Ndr;
use App\Entity\User;
use App\TestHelpers\BehatFixtures;
use App\TestHelpers\ClientTestHelper;
use Doctrine\ORM\EntityManagerInterface;

class FixtureHelper
{
    private BehatFixtures $behatFixtures;
    private EntityManagerInterface $em;

    public function __construct(BehatFixtures $behatFixtures, EntityManagerInterface $em)
    {
        $this->behatFixtures = $behatFixtures;
        $this->em = $em;
    }

    public function resetFixtures(string $testRunId)
    {
        return $this->behatFixtures->loadFixtures($testRunId);
    }

    public function getLoggedInUserDetails(string $email)
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => strtolower($email)]);

        $currentReport = $user->getFirstClient()->getCurrentReport();
        $previousReport = $user->getFirstClient()->getReports()[0];

        return [
            'email' => $user->getEmail(),
            'clientId' => $user->getFirstClient()->getId(),
            'currentReportId' => $currentReport->getId(),
            'currentReportType' => $currentReport->getType(),
            'currentReportNdrOrReport' => $currentReport instanceof Ndr ? 'ndr' : 'report',
            'previousReportId' => $previousReport->getId(),
            'previousReportType' => $previousReport->getType(),
            'previousReportNdrOrReport' => $previousReport instanceof Ndr ? 'ndr' : 'report',
        ];
    }

    public function duplicateClient(int $clientId)
    {
        $client = clone $this->em->getRepository(Client::class)->find($clientId);
        $client->setCaseNumber(ClientTestHelper::createValidCaseNumber());

        $this->em->persist($client);
        $this->em->flush();
    }
}

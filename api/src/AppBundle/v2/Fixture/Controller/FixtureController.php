<?php

namespace AppBundle\v2\Fixture\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\FixtureFactory\ClientFactory;
use AppBundle\FixtureFactory\ReportFactory;
use AppBundle\FixtureFactory\UserFactory;
use AppBundle\v2\Controller\ControllerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/fixture")
 */
class FixtureController
{
    use ControllerTrait;

    private $em;
    private $clientFactory;
    private $userFactory;
    private $organisationFactory;
    private $reportFactory;

    public function __construct(
        EntityManagerInterface $em,
        ClientFactory $clientFactory,
        UserFactory $userFactory,
        OrganisationFactory $organisationFactory,
        ReportFactory $reportFactory
    ) {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->userFactory = $userFactory;
        $this->organisationFactory = $organisationFactory;
        $this->reportFactory = $reportFactory;
    }

    /**
     * @Route("/court-order", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createCourtOrder(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $client = $this->createClient($fromRequest);
        $deputy = $this->createDeputy($fromRequest);
        $this->createReport($fromRequest, $client);

        if ($fromRequest['deputyType'] === User::TYPE_LAY) {
            $deputy->addClient($client);
        } else {
            $this->createOrgAndAttachParticipants($fromRequest, $deputy, $client);
        }

        $this->em->flush();

        return $this->buildSuccessResponse(['deputyEmail' => $deputy->getEmail()], 'Court order created', Response::HTTP_CREATED);
    }

    /**
     * @param $fromRequest
     * @return Client
     */
    private function createClient($fromRequest): Client
    {
        $client = $this->clientFactory->create([
            'id' => $fromRequest['caseNumber'],
            'courtDate' => $fromRequest['courtDate']
        ]);
        $this->em->persist($client);
        return $client;
    }

    /**
     * @param $fromRequest
     * @return User
     * @throws \Exception
     */
    private function createDeputy($fromRequest): User
    {
        $deputy = $this->userFactory->create([
            'id' => $fromRequest['deputyEmail'],
            'deputyType' => $fromRequest['deputyType'],
            'email' => $fromRequest['deputyEmail']
        ]);

        $this->em->persist($deputy);
        return $deputy;
    }

    /**
     * @param $fromRequest
     * @param Client $client
     * @throws \Exception
     */
    private function createReport($fromRequest, Client $client): void
    {
        $report = $this->reportFactory->create([
            'deputyType' => $fromRequest['deputyType'],
            'reportType' => $fromRequest['reportType'],
            'reportStatus' => $fromRequest['reportStatus']
        ], $client);

        $this->em->persist($report);
    }

    /**
     * @param $fromRequest
     * @param User $deputy
     * @param Client $client
     */
    private function createOrgAndAttachParticipants($fromRequest, User $deputy, Client $client): void
    {
        $organisation = $this->organisationFactory->createFromEmailIdentifier('Behat Org', $fromRequest['deputyEmail'], true);
        $organisation->addUser($deputy);
        $client->setOrganisation($organisation);
        $this->em->persist($organisation);
    }
}

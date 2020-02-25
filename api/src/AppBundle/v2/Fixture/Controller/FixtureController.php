<?php

namespace AppBundle\v2\Fixture\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\FixtureFactory\ClientFactory;
use AppBundle\FixtureFactory\ReportFactory;
use AppBundle\FixtureFactory\UserFactory;
use AppBundle\v2\Controller\ControllerTrait;
use AppBundle\v2\Fixture\ReportSection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    private $reportRepository;
    private $reportSection;
    private $deputyRepository;
    private $orgRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClientFactory $clientFactory,
        UserFactory $userFactory,
        OrganisationFactory $organisationFactory,
        ReportFactory $reportFactory,
        ReportRepository $reportRepository,
        ReportSection $reportSection,
        UserRepository $deputyRepository,
        OrganisationRepository $organisationRepository
    ) {
        $this->em = $em;
        $this->clientFactory = $clientFactory;
        $this->userFactory = $userFactory;
        $this->organisationFactory = $organisationFactory;
        $this->reportFactory = $reportFactory;
        $this->reportRepository = $reportRepository;
        $this->reportSection = $reportSection;
        $this->deputyRepository = $deputyRepository;
        $this->orgRepository = $organisationRepository;
    }

    /**
     * @Route("/court-order", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createCourtOrderAction(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);
        $client = $this->createClient($fromRequest);

        if (null === $deputy = $this->deputyRepository->findOneBy(['email' => strtolower($fromRequest['deputyEmail'])])) {
            $deputy = $this->createDeputy($fromRequest);
        }

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
        $uniqueOrgNameSegment = (preg_match('/\d+/', $fromRequest['deputyEmail'], $matches)) ? $matches[0] : rand(0,9999);
        $orgName = sprintf('Org %s Ltd', $uniqueOrgNameSegment);

        if (null === ($organisation = $this->orgRepository->findOneBy(['name' => $orgName]))) {
            $organisation = $this->organisationFactory->createFromEmailIdentifier($orgName, $fromRequest['deputyEmail'], true);
        }

        $organisation->addUser($deputy);
        $client->setNamedDeputy($this->buildNamedDeputy($deputy));
        $client->setOrganisation($organisation);
        $this->em->persist($organisation);
    }

    /**
     * @param User $deputy
     * @return NamedDeputy
     */
    private function buildNamedDeputy(User $deputy)
    {
        $namedDeputy = (new NamedDeputy())
            ->setFirstname($deputy->getFirstname())
            ->setLastname($deputy->getLastname())
            ->setEmail1($deputy->getEmail())
            ->setDeputyNo($deputy->getDeputyNo());

        $this->em->persist($namedDeputy);

        return $namedDeputy;
    }

    /**
     * @Route("/complete-sections/{reportId}", requirements={"id":"\d+"}, methods={"PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function completeReportSectionsAction(Request $request, $reportId)
    {
        if (null === $report = $this->reportRepository->find(intval($reportId))) {
            throw new NotFoundHttpException(sprintf('Report id %s not found', $reportId));
        }

        if (null === $sections = $request->query->get('sections')) {
            return $this->buildSuccessResponse([], 'Nothing updated', Response::HTTP_OK);
        }

        foreach (explode(',', $sections) as $section) {
            $this->reportSection->completeSection($report, $section);
        }

        $report->updateSectionsStatusCache($report->getAvailableSections());
        $this->em->flush();

        return $this->buildSuccessResponse([], 'Report updated', Response::HTTP_OK);
    }

    /**
     * Used for creating non-prof/pa users only as Org ID is required for those types
     *
     * @Route("/createUser", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createUser(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $deputy = $this->userFactory->create([
            'id' => $fromRequest['deputyEmail'],
            'deputyType' => $fromRequest['deputyType'],
            'email' => $fromRequest['deputyEmail'],
            'ndr' => $fromRequest['ndr'],
            'firstName' => $fromRequest['firstName'],
            'lastName' => $fromRequest['lastName'],
            'postCode' => $fromRequest['postCode'],
            'activated' => $fromRequest['activated']
        ]);

        $this->em->persist($deputy);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created', Response::HTTP_OK);
    }
}

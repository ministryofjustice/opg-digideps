<?php

namespace AppBundle\v2\Fixture\Controller;

use AppBundle\DataFixtures\DocumentSyncFixtures;
use AppBundle\Entity\CasRec;
use AppBundle\Entity\Client;
use AppBundle\Entity\Ndr\Ndr;
use AppBundle\Entity\Ndr\NdrRepository;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\NamedDeputy;
use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Entity\Repository\ReportRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Factory\OrganisationFactory;
use AppBundle\FixtureFactory\CasRecFactory;
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
    private $userRepository;
    private $ndrRepository;
    private $casRecFactory;

    public function __construct(
        EntityManagerInterface $em,
        ClientFactory $clientFactory,
        UserFactory $userFactory,
        OrganisationFactory $organisationFactory,
        ReportFactory $reportFactory,
        ReportRepository $reportRepository,
        ReportSection $reportSection,
        UserRepository $deputyRepository,
        OrganisationRepository $organisationRepository,
        UserRepository $userRepository,
        NdrRepository $ndrRepository,
        CasRecFactory $casRecFactory
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
        $this->userRepository = $userRepository;
        $this->ndrRepository = $ndrRepository;
        $this->casRecFactory = $casRecFactory;
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
            $deputyCasRec = $this->casRecFactory->create(
                [
                    'caseNumber' => $client->getCaseNumber(),
                    'clientLastName' => $client->getLastname(),
                    'deputyPostCode' => $deputy->getAddressPostcode(),
                    'deputyLastName' => $deputy->getLastname(),
                    'reportType' => $fromRequest['reportType']
                ]
            );

            $this->em->persist($deputyCasRec);
        }

        if (strtolower($fromRequest['reportType']) === 'ndr') {
            $this->createNdr($fromRequest, $client);
            $deputy->setNdrEnabled(true);
        } else {
            $this->createReport($fromRequest, $client);
        }

        if ($fromRequest['deputyType'] === User::TYPE_LAY) {
            $deputy->addClient($client);
        } else {
            $this->createOrgAndAttachParticipants($fromRequest, $deputy, $client);
        }

        if ($fromRequest['coDeputyEnabled']) {
            $deputy->setCoDeputyClientConfirmed(true);
            $coDeputy = $this->userFactory->createCoDeputy($deputy, $client, $fromRequest);

            $coDeputyCasRec = $this->casRecFactory->create(
                [
                    'caseNumber' => $client->getCaseNumber(),
                    'clientLastName' => $client->getLastname(),
                    'deputyPostCode' => $coDeputy->getAddressPostcode(),
                    'deputyLastName' => $coDeputy->getLastname(),
                    'reportType' => $fromRequest['reportType']
                ]
            );

            $this->em->persist($coDeputyCasRec);
            $this->em->persist($coDeputy);
        }

        $this->em->flush();

        $deputyIds = ['originalDeputy' => $deputy->getId()];

        if (isset($coDeputy)) {
            $deputyIds['coDeputy'] = $coDeputy->getId();
        }

        return $this->buildSuccessResponse(['deputyEmail' => $deputy->getEmail(), 'deputyIds' => $deputyIds], 'Court order created', Response::HTTP_CREATED);
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
            'email' => $fromRequest['deputyEmail'],
            'activated'=> $fromRequest['activated'],
            'coDeputyEnabled' => $fromRequest['coDeputyEnabled']
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
     * @param array $fromRequest
     * @param Client $client
     */
    private function createNdr(array $fromRequest, Client $client)
    {
        $ndr = new Ndr($client);

        $this->em->persist($ndr);

        if (isset($fromRequest['reportStatus']) && $fromRequest['reportStatus'] === Report::STATUS_READY_TO_SUBMIT) {
            foreach (['visits_care', 'expenses', 'income_benefits', 'bank_accounts', 'assets', 'debts', 'actions', 'other_info'] as $section) {
                $this->reportSection->completeSection($ndr, $section);
            }
        }
    }

    /**
     * @param $fromRequest
     * @param User $deputy
     * @param Client $client
     */
    private function createOrgAndAttachParticipants($fromRequest, User $deputy, Client $client): void
    {
        $uniqueOrgNameSegment = (preg_match('/\d+/', $fromRequest['deputyEmail'], $matches)) ? $matches[0] : rand(0, 9999);
        $orgName = sprintf('Org %s Ltd', $uniqueOrgNameSegment);

        if (null === ($organisation = $this->orgRepository->findOneBy(['name' => $orgName]))) {
            $organisation = $this->organisationFactory->createFromEmailIdentifier($orgName, $fromRequest['deputyEmail'], true);
        }

        $organisation->addUser($deputy);
        $client->setNamedDeputy($this->buildNamedDeputy($deputy, $fromRequest));
        $client->setOrganisation($organisation);
        $this->em->persist($organisation);
    }

    /**
     * @param User $deputy
     * @return NamedDeputy
     */
    private function buildNamedDeputy(User $deputy, array $fromRequest)
    {
        $namedDeputy = (new NamedDeputy())
            ->setFirstname($deputy->getFirstname())
            ->setLastname($deputy->getLastname())
            ->setEmail1($deputy->getEmail())
            ->setDeputyNo($deputy->getDeputyNo())
            ->setDeputyType($fromRequest['deputyType'] === 'PA' ? 23 : 21);

        $this->em->persist($namedDeputy);

        return $namedDeputy;
    }

    /**
     * @Route("/complete-sections/{reportType}/{reportId}", requirements={"id":"\d+"}, methods={"PUT"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function completeReportSectionsAction(Request $request, string $reportType, $reportId)
    {
        $repository = $reportType === 'ndr' ? $this->ndrRepository : $this->reportRepository;

        if (null === $report = $repository->find(intval($reportId))) {
            throw new NotFoundHttpException(sprintf('Report id %s not found', $reportId));
        }

        if (null === $sections = $request->query->get('sections')) {
            return $this->buildSuccessResponse([], 'Nothing updated', Response::HTTP_OK);
        }

        foreach (explode(',', $sections) as $section) {
            $this->reportSection->completeSection($report, $section);
        }

        if ($reportType === 'report') {
            $report->updateSectionsStatusCache($report->getAvailableSections());
        }

        $this->em->flush();

        return $this->buildSuccessResponse([], 'Report updated', Response::HTTP_OK);
    }

    /**
     * @Route("/createAdmin", methods={"POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     */
    public function createAdmin(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $deputy = $this->userFactory->createAdmin([
            'adminType' => $fromRequest['adminType'],
            'email' => $fromRequest['email'],
            'ndr' => $fromRequest['ndr'],
            'firstName' => $fromRequest['firstName'],
            'lastName' => $fromRequest['lastName'],
            'activated' => $fromRequest['activated']
        ]);

        $this->em->persist($deputy);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created');
    }

    /**
     * @Route("/getUserIDByEmail/{email}", methods={"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     */
    public function getUserIDByEmail(string $email)
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user !== null) {
            return $this->buildSuccessResponse(['id' => $user->getId()], 'User found', Response::HTTP_OK);
        } else {
            return $this->buildNotFoundResponse("Could not find user with email address '$email'");
        }
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

    /**
     * Used for deleting users to clean up after tests
     *
     * @Route("/deleteUser", methods={"POST"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function deleteUser(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy(array('email' => $fromRequest['email']));

        $this->em->remove($user);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User deleted', Response::HTTP_OK);
    }

    /**
     * @Route("/createClientAttachDeputy", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createClientAndAttachToDeputy(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $client = $this->clientFactory->create([
            "firstName" => $fromRequest['firstName'],
            "lastName" => $fromRequest['lastName'],
            "phone" => $fromRequest['phone'],
            "address" => $fromRequest['address'],
            "address2" => $fromRequest['address2'],
            "county" => $fromRequest['county'],
            "postCode" => $fromRequest['postCode'],
            "caseNumber" => $fromRequest['caseNumber'],
        ]);

        /** @var User $deputy */
        $deputy = $this->em->getRepository(User::class)->findOneBy(['email' => $fromRequest['deputyEmail']]);

        if ($deputy === null) {
            return $this->buildNotFoundResponse(sprintf("Could not find user with email address '%s'", $fromRequest['deputyEmail']));
        }

        $deputy->addClient($client);

        $this->em->persist($deputy);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created', Response::HTTP_OK);
    }

    /**
     * @Route("/createCasrec", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createCasrec(Request $request)
    {
        $fromRequest = json_decode($request->getContent(), true);

        $casRec = $this->casRecFactory->create($fromRequest);

        $data = [
            'caseNumber' => $casRec->getCaseNumber(),
            'clientLastName' => $casRec->getClientLastname(),
            'deputyLastName' => $casRec->getDeputySurname(),
            'deputyPostCode' => $casRec->getDeputyPostCode()
        ];

        if ($fromRequest['createCoDeputy']) {
            $coDeputy = $this->casRecFactory->createCoDeputy($casRec->getCaseNumber(), $fromRequest);
            $this->em->persist($coDeputy);
            $data['coDeputyLastName'] = $coDeputy->getDeputySurname();
            $data['coDeputyPostCode'] = $coDeputy->getDeputyPostCode();
        }

        $this->em->persist($casRec);
        $this->em->flush();


        return $this->buildSuccessResponse($data, 'CasRec row created', Response::HTTP_OK);
    }
}

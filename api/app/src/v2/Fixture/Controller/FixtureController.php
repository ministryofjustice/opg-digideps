<?php

namespace App\v2\Fixture\Controller;

use App\Entity\Client;
use App\Entity\Deputy;
use App\Entity\Ndr\Ndr;
use App\Entity\Organisation;
use App\Entity\Report\Report;
use App\Entity\User;
use App\Factory\OrganisationFactory;
use App\FixtureFactory\ClientFactory;
use App\FixtureFactory\PreRegistrationFactory;
use App\FixtureFactory\ReportFactory;
use App\FixtureFactory\UserFactory;
use App\Repository\NamedDeputyRepository;
use App\Repository\NdrRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\v2\Controller\ControllerTrait;
use App\v2\Fixture\ReportSection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/fixture")
 */
class FixtureController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private ClientFactory $clientFactory,
        private UserFactory $userFactory,
        private OrganisationFactory $organisationFactory,
        private ReportFactory $reportFactory,
        private ReportRepository $reportRepository,
        private ReportSection $reportSection,
        private UserRepository $deputyRepository,
        private OrganisationRepository $organisationRepository,
        private UserRepository $userRepository,
        private NdrRepository $ndrRepository,
        private PreRegistrationFactory $preRegistrationFactory,
        private NamedDeputyRepository $namedDeputyRepository,
        private string $symfonyEnvironment
    ) {
    }

    /**
     * @Route("/court-order", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function createCourtOrderAction(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);
        $fromRequest['courtDate'] = (new \DateTime('-366 days'))->format('Y-m-d');

        $client = $this->createClient($fromRequest);

        if (null === $deputy = $this->deputyRepository->findOneBy(['email' => strtolower($fromRequest['deputyEmail'])])) {
            $deputy = $this->createDeputy($fromRequest);
            $deputyPreRegistration = $this->preRegistrationFactory->create(
                [
                    'caseNumber' => $client->getCaseNumber(),
                    'clientLastName' => $client->getLastname(),
                    'deputyPostCode' => $deputy->getAddressPostcode(),
                    'deputyLastName' => $deputy->getLastname(),
                    'reportType' => $fromRequest['reportType'],
                ]
            );

            $this->em->persist($deputyPreRegistration);
        }

        if ('ndr' === strtolower($fromRequest['reportType'])) {
            $this->createNdr($fromRequest, $client);
            $deputy->setNdrEnabled(true);
        } else {
            $this->createReport($fromRequest, $client);
        }

        if (User::TYPE_LAY === $fromRequest['deputyType']) {
            $deputy->addClient($client);
        } else {
            $this->createOrgAndAttachParticipants($fromRequest, $deputy, $client);
        }

        if ($fromRequest['coDeputyEnabled']) {
            $deputy->setCoDeputyClientConfirmed(true);
            $coDeputy = $this->userFactory->createCoDeputy($deputy, $client, $fromRequest);

            $coDeputyPreRegistration = $this->preRegistrationFactory->create(
                [
                    'caseNumber' => $client->getCaseNumber(),
                    'clientLastName' => $client->getLastname(),
                    'deputyPostCode' => $coDeputy->getAddressPostcode(),
                    'deputyLastName' => $coDeputy->getLastname(),
                    'reportType' => $fromRequest['reportType'],
                ]
            );

            $this->em->persist($coDeputyPreRegistration);
            $this->em->persist($coDeputy);
        }

        $this->em->flush();

        $deputyIds = ['originalDeputy' => $deputy->getId()];

        if (isset($coDeputy)) {
            $deputyIds['coDeputy'] = $coDeputy->getId();
        }

        return $this->buildSuccessResponse(['deputyEmail' => $deputy->getEmail(), 'deputyIds' => $deputyIds], 'Court order created', Response::HTTP_CREATED);
    }

    private function createClient($fromRequest): Client
    {
        $client = $this->clientFactory->create([
            'id' => $fromRequest['caseNumber'],
            'courtDate' => $fromRequest['courtDate'],
        ]);
        $this->em->persist($client);

        return $client;
    }

    /**
     * @throws \Exception
     */
    private function createDeputy($fromRequest): User
    {
        $deputy = $this->userFactory->create([
            'id' => $fromRequest['deputyEmail'],
            'deputyType' => $fromRequest['deputyType'],
            'email' => $fromRequest['deputyEmail'],
            'activated' => $fromRequest['activated'],
            'coDeputyEnabled' => $fromRequest['coDeputyEnabled'],
        ]);

        $this->em->persist($deputy);

        return $deputy;
    }

    private function createNdr(array $fromRequest, Client $client)
    {
        $ndr = new Ndr($client);
        $client->setNdr($ndr);

        $this->em->persist($ndr);
        $this->em->persist($client);

        if (isset($fromRequest['reportStatus']) && Report::STATUS_READY_TO_SUBMIT === $fromRequest['reportStatus']) {
            foreach (['visits_care', 'expenses', 'income_benefits', 'bank_accounts', 'assets', 'debts', 'actions', 'other_info', 'client_benefits_check'] as $section) {
                $this->reportSection->completeSection($ndr, $section);
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function createReport($fromRequest, Client $client): void
    {
        $report = $this->reportFactory->create([
            'deputyType' => $fromRequest['deputyType'],
            'reportType' => $fromRequest['reportType'],
            'reportStatus' => $fromRequest['reportStatus'],
        ], $client);

        $this->em->persist($report);
    }

    private function createOrgAndAttachParticipants($fromRequest, User $deputy, Client $client): void
    {
        $uniqueOrgNameSegment = (preg_match('/\d+/', $fromRequest['deputyEmail'], $matches)) ? $matches[0] : rand(0, 9999);
        $orgName = sprintf('Org %s Ltd', $uniqueOrgNameSegment);

        if (null === ($organisation = $this->organisationRepository->findOneBy(['name' => $orgName]))) {
            $organisation = $this->organisationFactory->createFromEmailIdentifier($orgName, $fromRequest['deputyEmail'], true);
        }

        $organisation->addUser($deputy);

        if ($fromRequest['orgSizeUsers'] > 1 && !empty($fromRequest['orgSizeUsers'])) {
            foreach (range(1, $fromRequest['orgSizeUsers']) as $number) {
                $orgUser = $this->userFactory->createGenericOrgUser($organisation);
                $organisation->addUser($orgUser);
                $this->em->persist($orgUser);
            }
        }

        $namedDeputy = $this->buildNamedDeputy($deputy, $fromRequest);

        $client->setDeputy($namedDeputy);
        $client->setOrganisation($organisation);

        // if the org size is 1 but we want 10 clients still then create the clients but
        // we return so we don't create another 10 clients on top if we have a org size > 1
        if (1 === $fromRequest['orgSizeUsers'] && $fromRequest['orgSizeClients'] > 1 && !empty($fromRequest['orgSizeClients'])) {
            foreach (range(1, $fromRequest['orgSizeClients']) as $number) {
                $orgClient = $this->clientFactory->createGenericOrgClient($namedDeputy, $organisation, $fromRequest['courtDate']);
                $this->em->persist($orgClient);

                $this->createReport($fromRequest, $orgClient);
            }

            $this->em->persist($client);
            $this->em->persist($organisation);

            return;
        }

        if ($fromRequest['orgSizeUsers'] > 1 && !empty($fromRequest['orgSizeUsers'])) {
            foreach (range(1, $fromRequest['orgSizeClients']) as $number) {
                $orgClient = $this->clientFactory->createGenericOrgClient($namedDeputy, $organisation, $fromRequest['courtDate']);
                $this->em->persist($orgClient);

                $this->createReport($fromRequest, $orgClient);
            }
        }

        $this->em->persist($client);
        $this->em->persist($organisation);
    }

    /**
     * @return Deputy
     */
    private function buildNamedDeputy(User $deputy, array $fromRequest)
    {
        $namedDeputy = (new Deputy())
            ->setFirstname($deputy->getFirstname())
            ->setLastname($deputy->getLastname())
            ->setEmail1($deputy->getEmail())
            ->setDeputyUid($fromRequest['caseNumber'].mt_rand(1, 100))
            ->setAddress1($deputy->getAddress1())
            ->setAddressPostcode($deputy->getAddressPostcode())
            ->setPhoneMain($deputy->getPhoneMain());

        $this->em->persist($namedDeputy);

        return $namedDeputy;
    }

    /**
     * @Route("/complete-sections/{reportType}/{reportId}", requirements={"id":"\d+"}, methods={"PUT"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    public function completeReportSectionsAction(Request $request, string $reportType, $reportId)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $repository = 'ndr' === $reportType ? $this->ndrRepository : $this->reportRepository;

        if (null === $report = $repository->find(intval($reportId))) {
            throw new NotFoundHttpException(sprintf('Report id %s not found', $reportId));
        }

        if (null === $sections = $request->query->get('sections')) {
            return $this->buildSuccessResponse([], 'Nothing updated', Response::HTTP_OK);
        }

        foreach (explode(',', $sections) as $section) {
            $this->reportSection->completeSection($report, $section);
        }

        if ('report' === $reportType) {
            $report->updateSectionsStatusCache($report->getAvailableSections());
        }

        $this->em->flush();

        return $this->buildSuccessResponse([], 'Report updated', Response::HTTP_OK);
    }

    /**
     * @Route("/createAdmin", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function createAdmin(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $deputy = $this->userFactory->createAdmin([
            'adminType' => $fromRequest['adminType'],
            'email' => $fromRequest['email'],
            'ndr' => $fromRequest['ndr'],
            'firstName' => $fromRequest['firstName'],
            'lastName' => $fromRequest['lastName'],
            'activated' => $fromRequest['activated'],
        ]);

        $this->em->persist($deputy);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created');
    }

    /**
     * @Route("/getUserIDByEmail/{email}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function getUserIDByEmail(string $email)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null !== $user) {
            return $this->buildSuccessResponse(['id' => $user->getId()], 'User found', Response::HTTP_OK);
        } else {
            return $this->buildNotFoundResponse("Could not find user with email address '$email'");
        }
    }

    /**
     * Used for creating non-prof/pa users only as Org ID is required for those types.
     *
     * @Route("/createUser", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createUser(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $deputy = $this->userFactory->create([
            'id' => $fromRequest['deputyEmail'],
            'deputyType' => $fromRequest['deputyType'],
            'email' => $fromRequest['deputyEmail'],
            'ndr' => $fromRequest['ndr'],
            'firstName' => $fromRequest['firstName'],
            'lastName' => $fromRequest['lastName'],
            'postCode' => $fromRequest['postCode'],
            'activated' => $fromRequest['activated'],
        ]);

        $this->em->persist($deputy);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created', Response::HTTP_OK);
    }

    /**
     * Used for deleting users to clean up after tests.
     *
     * @Route("/deleteUser", methods={"POST"})
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function deleteUser(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $user = $this->userRepository->findOneBy(['email' => $fromRequest['email']]);

        $this->em->remove($user);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User deleted', Response::HTTP_OK);
    }

    /**
     * @Route("/createClientAttachDeputy", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createClientAndAttachToDeputy(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $client = $this->clientFactory->create([
            'firstName' => $fromRequest['firstName'],
            'lastName' => $fromRequest['lastName'],
            'phone' => $fromRequest['phone'],
            'address' => $fromRequest['address'],
            'address2' => $fromRequest['address2'],
            'county' => $fromRequest['county'],
            'postCode' => $fromRequest['postCode'],
            'caseNumber' => $fromRequest['caseNumber'],
        ]);

        /** @var User $deputy */
        $deputy = $this->userRepository->findOneBy(['email' => $fromRequest['deputyEmail']]);

        if (null === $deputy) {
            return $this->buildNotFoundResponse(sprintf("Could not find user with email address '%s'", $fromRequest['deputyEmail']));
        }

        $deputy->addClient($client);

        $this->em->persist($deputy);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created', Response::HTTP_OK);
    }

    /**
     * @Route("/createClientAttachOrgs", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createClientAndAttachToOrgs(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $client = $this->clientFactory->create([
            'firstName' => $fromRequest['firstName'],
            'lastName' => $fromRequest['lastName'],
            'phone' => $fromRequest['phone'],
            'address' => $fromRequest['address'],
            'address2' => $fromRequest['address2'],
            'county' => $fromRequest['county'],
            'postCode' => $fromRequest['postCode'],
            'caseNumber' => $fromRequest['caseNumber'],
        ]);

        /** @var Organisation $org */
        $org = $this->organisationRepository->findOneBy(['emailIdentifier' => $fromRequest['orgEmailIdentifier']]);

        if (is_null($org)) {
            return $this->buildNotFoundResponse(sprintf("Could not find org with email identifier '%s'", $fromRequest['orgEmailIdentifier']));
        }

        if (!empty($fromRequest['namedDeputyEmail'])) {
            $namedDeputy = $this->createNamedDeputyByExistingUser($fromRequest['namedDeputyEmail']);
            $client->setDeputy($namedDeputy);
        }

        $client->setOrganisation($org);
        $org->addClient($client);

        $this->em->persist($org);
        $this->em->persist($client);

        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created', Response::HTTP_OK);
    }

    private function createNamedDeputyByExistingUser(string $userEmail)
    {
        $namedDeputy = $this->namedDeputyRepository->findOneBy(['email1' => $userEmail]);

        if (is_null($namedDeputy)) {
            $user = $this->userRepository->findOneBy(['email' => $userEmail]);

            if ($user) {
                $namedDeputy = (new Deputy())
                    ->setDeputyUid(rand(8, 8))
                    ->setEmail1($user->getEmail())
                    ->setFirstname($user->getFirstname())
                    ->setLastname($user->getLastname());

                $this->em->persist($namedDeputy);

                return $namedDeputy;
            } else {
                return $this->buildNotFoundResponse(
                    sprintf(
                        "Could not find user or named Deputy with email identifier '%s'. Ensure one exists before using this function.",
                        $userEmail
                    )
                );
            }
        }
    }

    /**
     * @Route("/create-pre-registration", methods={"POST"})
     *
     * @Security("is_granted('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createPreRegistration(Request $request)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $preRegistration = $this->preRegistrationFactory->create($fromRequest);

        $data = [
            'caseNumber' => $preRegistration->getCaseNumber(),
            'clientLastName' => $preRegistration->getClientLastname(),
            'deputyLastName' => $preRegistration->getDeputySurname(),
            'deputyPostCode' => $preRegistration->getDeputyPostCode(),
        ];

        if ($fromRequest['createCoDeputy']) {
            $coDeputy = $this->preRegistrationFactory->createCoDeputy($preRegistration->getCaseNumber(), $fromRequest);
            $this->em->persist($coDeputy);
            $data['coDeputyLastName'] = $coDeputy->getDeputySurname();
            $data['coDeputyPostCode'] = $coDeputy->getDeputyPostCode();
        }

        $this->em->persist($preRegistration);
        $this->em->flush();

        return $this->buildSuccessResponse($data, 'PreRegistration row created', Response::HTTP_OK);
    }

    /**
     * @Route("/move-users-clients-to-users-org/{userEmail}", name="move_users_clients_to_org", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return JsonResponse
     */
    public function moveUsersClientsToUsersOrg(string $userEmail)
    {
        if ('prod' === $this->symfonyEnvironment) {
            throw $this->createNotFoundException();
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $userEmail]);

        if (is_null($user)) {
            $this->buildErrorResponse("User $userEmail not found");
        }

        $clients = $user->getClients();

        foreach ($clients as $client) {
            if (!$user->getOrganisations()->first()) {
                $this->buildErrorResponse("User $userEmail has no Organisations associated with them");
            }

            $client->setOrganisation($user->getOrganisations()[0]);
            $this->em->persist($client);
        }

        $this->em->flush();

        return $this->buildSuccessResponse([json_encode($clients, JSON_PRETTY_PRINT)], 'Clients added to Users first Org', Response::HTTP_OK);
    }

    /**
     * @Route("/activateOrg/{orgName}", name="activate_org", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @return JsonResponse
     */
    public function activateOrg(string $orgName)
    {
        try {
            if ('prod' === $this->symfonyEnvironment) {
                throw $this->createNotFoundException();
            }

            /** @var Organisation $org */
            $org = $this->organisationRepository->findOneBy(['name' => $orgName]);

            if (is_null($org)) {
                $this->buildErrorResponse("Org '$orgName' not found");
            }

            $org->setIsActivated(true);
            $this->em->persist($org);
            $this->em->flush();

            return $this->buildSuccessResponse([json_encode($org, JSON_PRETTY_PRINT)], "Org '$orgName' activated", Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->buildErrorResponse(sprintf("Organisation '%s' could not be activated: %s", $orgName, $e->getMessage()));
        }
    }
}

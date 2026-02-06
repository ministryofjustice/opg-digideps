<?php

namespace App\v2\Fixture\Controller;

use App\Entity\Client;
use App\Entity\CourtOrder;
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
use App\Repository\DeputyRepository;
use App\Repository\NdrRepository;
use App\Repository\OrganisationRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use App\v2\Controller\ControllerTrait;
use App\v2\Fixture\ReportSection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/fixture')]
class FixtureController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ClientFactory $clientFactory,
        private readonly UserFactory $userFactory,
        private readonly OrganisationFactory $organisationFactory,
        private readonly ReportFactory $reportFactory,
        private readonly ReportRepository $reportRepository,
        private readonly ReportSection $reportSection,
        private readonly OrganisationRepository $organisationRepository,
        private readonly UserRepository $userRepository,
        private readonly NdrRepository $ndrRepository,
        private readonly PreRegistrationFactory $preRegistrationFactory,
        private readonly DeputyRepository $deputyRepository,
        private readonly bool $fixturesEnabled,
    ) {
    }

    /** * @throws \Exception */
    #[Route(path: '/court-order', methods: ['POST'])] #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function createCourtOrder(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }
        $fromRequest = (array) json_decode($request->getContent(), true);
        $fromRequest['courtDate'] = (new \DateTime('-366 days'))->format('Y-m-d');
        $client = $this->generateClient($fromRequest);
        $user = new User();

        $multiClientDeputy = [];
        if (!$fromRequest['multiClientEnabled']) {
            $user = $this->createSingleClientDeputy($fromRequest, $client);
            if ($fromRequest['coDeputyEnabled']) {
                $coDeputy = $this->createCoDeputy($user, $fromRequest, $client);
            }
        } else {
            $multiClientDeputy = $this->createMultiClientDeputy($fromRequest, $client);
        }

        $this->em->flush();
        $deputyIds = !$fromRequest['multiClientEnabled'] ? ['originalDeputy' => $user->getId()] : $multiClientDeputy['deputyIds'];
        if (!$fromRequest['multiClientEnabled'] && isset($coDeputy)) {
            $deputyIds['coDeputy'] = $coDeputy->getId();
        }
        if (!$fromRequest['multiClientEnabled']) {
            return $this->buildSuccessResponse(['deputyEmail' => $user->getEmail(), 'deputyIds' => $deputyIds, 'Court order created', Response::HTTP_CREATED]);
        } else {
            return $this->buildSuccessResponse(['deputyEmail' => $user->getEmail(), 'deputyIds' => $deputyIds, 'multiClientCaseNumbers' => $multiClientDeputy['multiClientCaseNumbers']], 'Court order created', Response::HTTP_CREATED);
        }
    }

    /** * @throws \Exception */
    private function createSingleClientDeputy(mixed $fromRequest, Client $client): User
    {
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }

        // Validate 'deputyEmail'
        if (!isset($fromRequest['deputyEmail']) || !is_string($fromRequest['deputyEmail'])) {
            throw new \InvalidArgumentException('Missing or invalid "deputyEmail" field in request.');
        }

        $email = strtolower($fromRequest['deputyEmail']);

        // Check if user already exists
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user instanceof User) {
            $user = $this->generateUser($fromRequest);
            $deputyPreRegistration = $this->generatePreRegistration($fromRequest, $client, $user);
            $this->em->persist($deputyPreRegistration);
        }

        $deputy = $this->generateDeputy($user);
        $courtOrder = $this->generateCourtOrder($client);

        $deputy->associateWithCourtOrder($courtOrder);
        $this->em->persist($deputy);

        if (!isset($fromRequest['reportType']) || !is_string($fromRequest['reportType'])) {
            throw new \InvalidArgumentException('Missing or invalid "reportType" field in request.');
        }

        $reportType = strtolower($fromRequest['reportType']);

        if ('ndr' === $reportType) {
            $this->createNdr($fromRequest, $client);
            $user->setNdrEnabled(true);
        } else {
            $report = $this->generateReport($fromRequest, $client);
            $this->em->persist($report);
            $courtOrder->addReport($report);
            $this->em->persist($courtOrder);
        }

        if (User::TYPE_LAY === $fromRequest['deputyType']) {
            $user->setIsPrimary(true);
            $user->addClient($client);
        } else {
            $this->createOrgAndAttachParticipants($fromRequest, $user, $client);
        }

        return $user;
    }

    /** * @throws \Exception */
    private function createMultiClientDeputy(mixed $fromRequest, Client $client): array
    {
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }
        // We create 1 deputy and 2 court orders each with a client attached to the court orders

        // Generate primary user - update email to clearly show it's a multi-client
        $fromRequest['deputyEmail'] = str_replace(['original-lay-deputy'], 'lay-multi-client-deputy-primary', $fromRequest['deputyEmail']);
        $user = $this->generateUser($fromRequest);
        $user->setIsPrimary(true);
        $user->addClient($client);

        // Generate initial deputy from the user
        $deputy = $this->generateDeputy($user);

        $deputyPreRegistration = $this->generatePreRegistration($fromRequest, $client, $user);
        $courtOrder = $this->generateCourtOrder($client);

        $deputy->associateWithCourtOrder($courtOrder);
        $this->em->persist($deputy);
        $report = $this->generateReport($fromRequest, $client);
        $this->em->persist($report);
        $courtOrder->addReport($report);
        $this->em->persist($courtOrder);
        $this->em->persist($deputyPreRegistration);
        $this->em->persist($user);
        // Attach co-deputy to the primary client
        if ($fromRequest['coDeputyEnabled']) {
            $coDeputy = $this->createCoDeputy($user, $fromRequest, $client);
        }

        // create second deputy account and client
        $fromRequest['deputyEmail'] = str_replace(['lay-multi-client-deputy-primary'], 'lay-multi-client-deputy-secondary', $fromRequest['deputyEmail']);
        $secondUser = $this->generateUser($fromRequest);
        // update case number for second client
        $fromRequest['caseNumber'] = substr($fromRequest['caseNumber'], 0, -3) . rand(100, 999);
        $secondClient = $this->generateClient($fromRequest);
        $secondCourtOrder = $this->generateCourtOrder($secondClient);
        $secondDeputyPreRegistration = $this->generatePreRegistration($fromRequest, $secondClient, $secondUser);
        $secondUser->addClient($secondClient);

        // Associate second deputy to original court order
        $deputy->associateWithCourtOrder($secondCourtOrder);
        $this->em->persist($deputy);
        $secondReport = $this->generateReport($fromRequest, $secondClient);
        $this->em->persist($secondReport);
        $secondCourtOrder->addReport($secondReport);
        $this->em->persist($secondCourtOrder);
        $this->em->persist($secondDeputyPreRegistration);
        $this->em->persist($secondUser);

        // Return created deputies
        $this->em->flush();
        $deputyIds = ['original deputy' => $user->getId(), 'second deputy' => $secondUser->getId()];
        if (isset($coDeputy)) {
            $deputyIds['coDeputy'] = $coDeputy->getId();
        }

        return ['deputyIds' => $deputyIds, 'multiClientCaseNumbers' => [$deputyPreRegistration->getCaseNumber(), $secondDeputyPreRegistration->getCaseNumber()]];
    }

    private function generateClient(mixed $fromRequest): Client
    {
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }
        $client = $this->clientFactory->create([
            'id' => $fromRequest['caseNumber'],
            'courtDate' => $fromRequest['courtDate'],
        ]);
        $this->em->persist($client);

        return $client;
    }

    private function generatePreRegistration(mixed $fromRequest, Client $client, User $user): \App\Entity\PreRegistration
    {
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }
        return $this->preRegistrationFactory->create(
            [
                'caseNumber' => $client->getCaseNumber(),
                'clientLastName' => $client->getLastname(),
                'deputyPostCode' => $user->getAddressPostcode(),
                'deputyLastName' => $user->getLastname(),
                'deputyFirstName' => $user->getFirstName(),
                'reportType' => $fromRequest['reportType'],
                'deputyUid' => $fromRequest['deputyUid'],
            ]
        );
    }

    private function generateDeputy(User $user): Deputy
    {
        $uid = $user->getDeputyUid();
        if ($uid === null) {
            throw new \InvalidArgumentException('Deputy UID is missing for user ' . $user->getId());
        }

        return (new Deputy())
            ->setDeputyUid((string) $uid)
            ->setUser($user)
            ->setEmail1($user->getEmail())
            ->setFirstname($user->getFirstname())
            ->setLastname($user->getLastname());
    }

    private function generateCourtOrder(Client $client): CourtOrder
    {
        $courtOrder = new CourtOrder();

        $courtOrder->setCourtOrderUid(strval(rand(100000000000, 999999999999)));
        $courtOrder->setOrderType('hw');
        $courtOrder->setStatus('ACTIVE');
        $courtOrder->setOrderMadeDate(new \DateTime('2020-06-14'));
        $courtOrder->setClient($client);
        $courtOrder->setCreatedAt(new \DateTime());
        $courtOrder->setUpdatedAt(new \DateTime());

        return $courtOrder;
    }

    /**
     * @throws \Exception
     */
    private function generateUser(mixed $fromRequest): User
    {
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }
        $user = $this->userFactory->create([
            'id' => $fromRequest['deputyEmail'],
            'deputyType' => $fromRequest['deputyType'],
            'email' => $fromRequest['deputyEmail'],
            'activated' => $fromRequest['activated'],
            'coDeputyEnabled' => $fromRequest['coDeputyEnabled'],
            'deputyUid' => $fromRequest['deputyUid'],
        ]);

        $this->em->persist($user);

        return $user;
    }

    private function createNdr(array $fromRequest, Client $client): void
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
    private function generateReport(mixed $fromRequest, Client $client): Report
    {
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }
        return $this->reportFactory->create([
            'deputyType' => $fromRequest['deputyType'],
            'reportType' => $fromRequest['reportType'],
            'reportStatus' => $fromRequest['reportStatus'],
        ], $client);
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

        $deputy = $this->buildDeputy($deputy, $fromRequest);

        $client->setDeputy($deputy);
        $client->setOrganisation($organisation);

        // if the org size is 1 but we want 10 clients still then create the clients but
        // we return so we don't create another 10 clients on top if we have a org size > 1
        if (1 === $fromRequest['orgSizeUsers'] && $fromRequest['orgSizeClients'] > 1 && !empty($fromRequest['orgSizeClients'])) {
            foreach (range(1, $fromRequest['orgSizeClients']) as $number) {
                $orgClient = $this->clientFactory->createGenericOrgClient($deputy, $organisation, $fromRequest['courtDate']);
                $this->em->persist($orgClient);

                $report = $this->generateReport($fromRequest, $orgClient);
                $this->em->persist($report);
            }

            $this->em->persist($client);
            $this->em->persist($organisation);

            return;
        }

        if ($fromRequest['orgSizeUsers'] > 1 && !empty($fromRequest['orgSizeUsers'])) {
            foreach (range(1, $fromRequest['orgSizeClients']) as $number) {
                $orgClient = $this->clientFactory->createGenericOrgClient($deputy, $organisation, $fromRequest['courtDate']);
                $this->em->persist($orgClient);

                $report = $this->generateReport($fromRequest, $orgClient);
                $this->em->persist($report);
            }
        }

        $this->em->persist($client);
        $this->em->persist($organisation);
    }

    private function buildDeputy(User $deputy, array $fromRequest): Deputy
    {
        $deputy = (new Deputy())
            ->setFirstname($deputy->getFirstname())
            ->setLastname($deputy->getLastname())
            ->setEmail1($deputy->getEmail())
            ->setDeputyUid('70' . str_pad($fromRequest['caseNumber'] . mt_rand(1, 100), 10))
            ->setAddress1($deputy->getAddress1())
            ->setAddressPostcode($deputy->getAddressPostcode())
            ->setPhoneMain($deputy->getPhoneMain());

        $this->em->persist($deputy);

        return $deputy;
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/complete-sections/{reportType}/{reportId}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function completeReportSections(Request $request, string $reportType, int $reportId): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $repository = 'ndr' === $reportType ? $this->ndrRepository : $this->reportRepository;

        if (null === $report = $repository->find($reportId)) {
            throw new NotFoundHttpException(sprintf('Report id %s not found', $reportId));
        }

        if (null === $sections = $request->query->get('sections')) {
            return $this->buildSuccessResponse([], 'Nothing updated');
        }

        foreach (explode(',', $sections) as $section) {
            $this->reportSection->completeSection($report, $section);
        }

        if ('report' === $reportType) {
            $report->updateSectionsStatusCache($report->getAvailableSections());
        }

        $this->em->flush();

        return $this->buildSuccessResponse([], 'Report updated');
    }

    #[Route(path: '/createAdmin', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function createAdmin(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
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

    #[Route(path: '/getUserIDByEmail/{email}', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function getUserIDByEmail(string $email): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null !== $user && $user instanceof User) {
            return $this->buildSuccessResponse(['id' => $user->getId()], 'User found');
        } else {
            return $this->buildNotFoundResponse("Could not find user with email address '$email'");
        }
    }

    /**
     * Used for creating non-prof/pa users only as Org ID is required for those types.
     */
    #[Route(path: '/createUser', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createUser(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }

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

        return $this->buildSuccessResponse($fromRequest, 'User created');
    }

    /**
     * Used for deleting users to clean up after tests.
     */
    #[Route(path: '/deleteUser', methods: ['POST'])]
    #[IsGranted(attribute: 'ROLE_SUPER_ADMIN')]
    public function deleteUser(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);

        $user = $this->userRepository->findOneBy(['email' => $fromRequest['email']]);

        $this->em->remove($user);
        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User deleted');
    }

    #[Route(path: '/createClientAttachDeputy', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createClientAndAttachToDeputy(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
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

        return $this->buildSuccessResponse($fromRequest, 'User created');
    }

    #[Route(path: '/createClientAttachOrgs', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createClientAndAttachToOrgs(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }

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

        if (!empty($fromRequest['deputyEmail'])) {
            $deputy = $this->createUserByExistingUser($fromRequest['deputyEmail']);
            $client->setDeputy($deputy);
        }

        $client->setOrganisation($org);
        $org->addClient($client);

        $this->em->persist($org);
        $this->em->persist($client);

        $this->em->flush();

        return $this->buildSuccessResponse($fromRequest, 'User created');
    }

    private function createUserByExistingUser(string $userEmail): Deputy|null
    {
        $deputy = $this->deputyRepository->findOneBy(['email1' => $userEmail]);

        if (is_null($deputy)) {
            $user = $this->userRepository->findOneBy(['email' => $userEmail]);

            if ($user) {
                $deputy = (new Deputy())
                    ->setDeputyUid(rand(8, 8))
                    ->setEmail1($user->getEmail())
                    ->setFirstname($user->getFirstname())
                    ->setLastname($user->getLastname());

                $this->em->persist($deputy);

                return $deputy;
            } else {
                throw new \RuntimeException(
                    sprintf(
                        "Could not find user or deputy with email identifier '%s'. Ensure one exists before using this function.",
                        $userEmail
                    )
                );
            }
        }
        return null;
    }

    #[Route(path: '/create-pre-registration', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN', 'ROLE_AD')"))]
    public function createPreRegistration(Request $request): JsonResponse
    {
        if (!$this->fixturesEnabled) {
            throw $this->createNotFoundException();
        }

        $fromRequest = json_decode($request->getContent(), true);
        if (!is_array($fromRequest)) {
            throw new \InvalidArgumentException('Invalid request payload: expected array.');
        }

        $preRegistration = $this->preRegistrationFactory->create($fromRequest);

        $primaryData = [
            'caseNumber' => $preRegistration->getCaseNumber(),
            'clientLastName' => $preRegistration->getClientLastname(),
            'deputyLastName' => $preRegistration->getDeputySurname(),
            'deputyFirstName' => $preRegistration->getDeputyFirstname(),
            'deputyPostCode' => $preRegistration->getDeputyPostCode(),
        ];

        if ($fromRequest['createCoDeputy']) {
            $coDeputy = $this->preRegistrationFactory->createCoDeputy($preRegistration->getCaseNumber(), $fromRequest);
            $this->em->persist($coDeputy);
            $primaryData['coDeputyLastName'] = $coDeputy->getDeputySurname();
            $primaryData['coDeputyFirstName'] = $coDeputy->getDeputyFirstname();
            $primaryData['coDeputyPostCode'] = $coDeputy->getDeputyPostCode();
        }

        $data[] = $primaryData;
        $this->em->persist($preRegistration);

        if ($fromRequest['multiClientEnabled']) {
            $preRegistrationSecondaryClient = $this->preRegistrationFactory->create([
                'deputyUid' => $preRegistration->getDeputyUid(),
                'clientFirstName' => 'Joe',
                'clientLastName' => 'Snow',
                $fromRequest,
            ]);
            $this->em->persist($preRegistrationSecondaryClient);

            $data[] = [
                'caseNumber' => $preRegistrationSecondaryClient->getCaseNumber(),
                'clientLastName' => $preRegistrationSecondaryClient->getClientLastname(),
                'deputyLastName' => $preRegistrationSecondaryClient->getDeputySurname(),
                'deputyFirstName' => $preRegistrationSecondaryClient->getDeputyFirstname(),
                'deputyPostCode' => $preRegistrationSecondaryClient->getDeputyPostCode(),
            ];
        }

        $this->em->flush();

        return $this->buildSuccessResponse($data, 'PreRegistration rows created');
    }

    #[Route(path: '/move-users-clients-to-users-org/{userEmail}', name: 'move_users_clients_to_org', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function moveUsersClientsToUsersOrg(string $userEmail): JsonResponse
    {
        if (!$this->fixturesEnabled) {
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

        return $this->buildSuccessResponse([json_encode($clients, JSON_PRETTY_PRINT)], 'Clients added to Users first Org');
    }

    #[Route(path: '/activateOrg/{orgName}', name: 'activate_org', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_ADMIN')]
    public function activateOrg(string $orgName): JsonResponse
    {
        try {
            if (!$this->fixturesEnabled) {
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

            return $this->buildSuccessResponse([json_encode($org, JSON_PRETTY_PRINT)], "Org '$orgName' activated");
        } catch (\Throwable $e) {
            return $this->buildErrorResponse(sprintf("Organisation '%s' could not be activated: %s", $orgName, $e->getMessage()));
        }
    }

    private function createCoDeputy(User $deputy, array $fromRequest, Client $client): User
    {
        $deputy->setCoDeputyClientConfirmed(true);
        $coDeputy = $this->userFactory->createCoDeputy($deputy, $client, $fromRequest);

        $coDeputyPreRegistration = $this->preRegistrationFactory->create(
            [
                'caseNumber' => $client->getCaseNumber(),
                'clientLastName' => $client->getLastname(),
                'deputyPostCode' => $coDeputy->getAddressPostcode(),
                'deputyLastName' => $coDeputy->getLastname(),
                'deputyFirstName' => $coDeputy->getFirstName(),
                'reportType' => $fromRequest['reportType'],
                'deputyUid' => $coDeputy->getDeputyUid(),
            ]
        );

        $this->em->persist($coDeputyPreRegistration);
        $this->em->persist($coDeputy);
        $this->em->flush();

        $coDeputyRecord = $this->generateDeputy($coDeputy);

        $deputyRecord = $this->deputyRepository->findOneBy(['email1' => $deputy->getEmail()]);
        if (!$deputyRecord instanceof Deputy) {
            throw new \RuntimeException('No deputy found with email: ' . $deputy->getEmail());
        }

        $courtOrder = $deputyRecord->getCourtOrdersWithStatus()[0]['courtOrder'] ?? null;
        if (empty($courtOrder)) {
            throw new \RuntimeException('No court order found for deputy with email ' . $deputy->getEmail());
        }

        $coDeputyRecord->associateWithCourtOrder($courtOrder);
        $this->em->persist($coDeputyRecord);
        $this->em->flush();

        return $coDeputy;
    }
}

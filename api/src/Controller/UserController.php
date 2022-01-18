<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Security\UserVoter;
use App\Service\Auth\AuthService;
use App\Service\Formatter\RestFormatter;
use App\Service\UserService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Security as SecurityHelper;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends RestController
{
    private UserService $userService;
    private EncoderFactoryInterface $encoderFactory;
    private UserRepository $userRepository;
    private ClientRepository $clientRepository;
    private UserVoter $userVoter;
    private SecurityHelper $securityHelper;
    private EntityManagerInterface $em;
    private AuthService $authService;
    private RestFormatter $formatter;

    public function __construct(
        UserService $userService,
        EncoderFactoryInterface $encoderFactory,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        UserVoter $userVoter,
        SecurityHelper $securityHelper,
        EntityManagerInterface $em,
        AuthService $authService,
        RestFormatter $formatter
    ) {
        $this->userService = $userService;
        $this->encoderFactory = $encoderFactory;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->userVoter = $userVoter;
        $this->securityHelper = $securityHelper;
        $this->em = $em;
        $this->authService = $authService;
        $this->formatter = $formatter;
    }

    /**
     * @Route("", methods={"POST"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD') or is_granted('ROLE_ORG_NAMED') or is_granted('ROLE_ORG_ADMIN')")
     */
    public function add(Request $request)
    {
        $data = $this->formatter->deserializeBodyContent($request, [
            'role_name' => 'notEmpty',
            'email' => 'notEmpty',
            'firstname' => 'mustExist',
            'lastname' => 'mustExist',
        ]);

        /** @var User $newUser */
        $newUser = $this->populateUser(new User(), $data);

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $this->userService->addUser($loggedInUser, $newUser, $data);

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user', 'user-teams', 'team'];
        $this->formatter->setJmsSerialiserGroups($groups);

        return $newUser;
    }

    /**
     * call setters on User when $data contains values.
     * //TODO move to service.
     */
    private function populateUser(User $user, array $data)
    {
        // Cannot easily(*) use JSM deserialising with already constructed objects.                                                                                                                                                             +
        // Also. It'd be possible to differentiate when a NULL value is intentional or not
        // (*) see options here https://github.com/schmittjoh/serializer/issues/79
        // http://jmsyst.com/libs/serializer/master/event_system

        $this->hydrateEntityWithArrayData($user, $data, [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail',
            'address1' => 'setAddress1',
            'address2' => 'setAddress2',
            'address3' => 'setAddress3',
            'address_postcode' => 'setAddressPostcode',
            'address_country' => 'setAddressCountry',
            'phone_alternative' => 'setPhoneAlternative',
            'phone_main' => 'setPhoneMain',
            'ndr_enabled' => 'setNdrEnabled',
            'ad_managed' => 'setAdManaged',
            'role_name' => 'setRoleName',
            'job_title' => 'setJobTitle',
            'co_deputy_client_confirmed' => 'setCoDeputyClientConfirmed',
        ]);

        if (array_key_exists('last_logged_in', $data)) {
            $user->setLastLoggedIn(new DateTime($data['last_logged_in']));
        }

        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new DateTime($data['token_date']));
        }

        if (!empty($data['role_name'])) {
            $roleToSet = $data['role_name'];
            $user->setRoleName($roleToSet);
        }

        return $user;
    }

    /**
     * @Route("/{id}", methods={"PUT"})
     */
    public function update(Request $request, $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if (
            $loggedInUser->getId() != $requestedUser->getId()
            && !$this->isGranted(User::ROLE_ADMIN)
            && !$this->isGranted(User::ROLE_AD)
            && !$this->isGranted(User::ROLE_ORG_NAMED)
            && !$this->isGranted(User::ROLE_ORG_ADMIN)
        ) {
            throw $this->createAccessDeniedException("Non-admin not authorised to change other user's data");
        }

        /** @var User $originalUser */
        $originalUser = clone $requestedUser;

        $data = $this->formatter->deserializeBodyContent($request);
        $this->populateUser($requestedUser, $data);

        // check if rolename in data - if so add audit log
        $this->userService->editUser($originalUser, $requestedUser);

        if (null !== $requestedUser->getRegistrationToken()) {
            $requestedUser->setRegistrationToken(null);
            $this->em->persist($requestedUser);
            $this->em->flush();
        }

        return ['id' => $requestedUser->getId()];
    }

    /**
     * @Route("/{id}/is-password-correct", methods={"POST"})
     */
    public function isPasswordCorrect(Request $request, $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if ($loggedInUser->getId() != $requestedUser->getId()) {
            throw $this->createAccessDeniedException("Not authorised to check other user's password");
        }

        $data = $this->formatter->deserializeBodyContent($request, [
            'password' => 'notEmpty',
        ]);

        $oldPassword = $this->encoderFactory->getEncoder($requestedUser)->encodePassword($data['password'], $requestedUser->getSalt());

        return $oldPassword == $requestedUser->getPassword();
    }

    /**
     * change password, activate user and send remind email.
     *
     * @Route("/{id}/set-password", methods={"PUT"})
     */
    public function changePassword(Request $request, $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if ($loggedInUser->getId() != $requestedUser->getId()) {
            throw $this->createAccessDeniedException("Not authorised to change other user's data");
        }

        $data = $this->formatter->deserializeBodyContent($request, [
            'password_plain' => 'notEmpty',
        ]);

        $newPassword = $this->encoderFactory->getEncoder($requestedUser)->encodePassword($data['password_plain'], $requestedUser->getSalt());

        $requestedUser->setPassword($newPassword);

        if (array_key_exists('set_active', $data)) {
            $requestedUser->setActive($data['set_active']);
        }

        $this->em->flush();

        return $requestedUser->getId();
    }

    /**
     * change email.
     *
     * @Route("/{id}/update-email", methods={"PUT"})
     */
    public function changeEmail(Request $request, $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User $requestedUser */
        $requestedUser = $this->findEntityBy(User::class, $id, 'User not found');

        if ($loggedInUser->getId() != $requestedUser->getId()) {
            throw $this->createAccessDeniedException("Not authorised to change other user's data");
        }

        $data = $this->formatter->deserializeBodyContent($request, [
            'updated_email' => 'notEmpty',
        ]);

        $requestedUser->setEmail($data['updated_email']);

        $this->em->flush();

        return $requestedUser->getId();
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        return $this->getOneByFilter($request, 'user_id', $id);
    }

    /**
     * @Route("/get-one-by/{what}/{filter}", requirements={
     *   "what" = "(user_id|email|case_number)"
     * }, methods={"GET"})
     */
    public function getOneByFilter(Request $request, $what, $filter)
    {
        if ('email' == $what) {
            /** @var User|null $user */
            $user = $this->userRepository->findOneBy(['email' => strtolower($filter)]);
            if (!$user) {
                throw new RuntimeException('User not found', 404);
            }
        } elseif ('case_number' == $what) {
            /** @var Client|null $client */
            $client = $this->clientRepository->findOneBy(['caseNumber' => $filter]);
            if (!$client) {
                throw new RuntimeException('Client not found', 404);
            }
            if (empty($client->getUsers())) {
                throw new RuntimeException('Client has not users', 404);
            }
            $user = $client->getUsers()[0];
        } elseif ('user_id' == $what) {
            /** @var User|null $user */
            $user = $this->userRepository->find($filter);
            if (!$user) {
                throw new RuntimeException('User not found', 419);
            }
        } else {
            throw new RuntimeException('wrong query', 500);
        }

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];

        $this->formatter->setJmsSerialiserGroups($groups);

        if ($loggedInUser->isCoDeputyWith($user)) {
            return $user;
        }

        $requestedUserIsLogged = $loggedInUser->getId() == $user->getId();

        // only allow admins to access any user, otherwise the user can only see himself
        if (
            !$this->isGranted(User::ROLE_ADMIN)
            && !$this->isGranted(User::ROLE_AD)
            && !$requestedUserIsLogged
        ) {
            throw $this->createAccessDeniedException("Not authorised to see other user's data");
        }

        return $user;
    }

    /**
     * Get user by email, and retrieve only id and team names the user belongs to.
     * Only for ROLE_PROF named and admin, when adding users to multiple teams.
     * Returns empty if user doesn't exist.
     *
     * @Route("/get-team-names-by-email/{email}", methods={"GET"})
     * @Security("is_granted('ROLE_ORG_NAMED') or is_granted('ROLE_ORG_ADMIN')")
     */
    public function getUserTeamNames(Request $request, $email)
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        $this->formatter->setJmsSerialiserGroups(['user-id', 'team-names']);

        return $user;
    }

    /**
     * Delete user with clients.
     *
     * @Route("/{id}", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     *
     * @param int $id
     *
     * @return array
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete($id)
    {
        /** @var User $user */
        $deletee = $this->userRepository->find($id);

        /** @var TokenInterface $user */
        $token = $this->securityHelper->getToken();

        $canDelete = $this->userVoter->vote($token, $deletee, [UserVoter::DELETE_USER]);

        if (UserVoter::ACCESS_DENIED === $canDelete) {
            $errMessage = sprintf('A %s cannot delete a %s', $token->getUser()->getRoleName(), $deletee->getRoleName());
            throw $this->createAccessDeniedException($errMessage);
        }

        if ($deletee->getFirstClient()) {
            $clients = $deletee->getClients();
            $this->em->remove($clients[0]);
        }

        $this->em->remove($deletee);
        $this->em->flush();

        return [];
    }

    /**
     * @Route("/get-all", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function getAll(Request $request)
    {
        $this->formatter->setJmsSerialiserGroups(['user']);

        return $this->userRepository->findUsersByQueryParameters($request);
    }

    /**
     * Requires client secret.
     *
     * @Route("/recreate-token/{email}", defaults={"email": "none"}, methods={"PUT"})
     */
    public function recreateToken(Request $request, $email)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new RuntimeException('client secret not accepted.', 403);
        }

        /** @var User $user */
        $user = $this->findEntityBy(User::class, ['email' => strtolower($email)]);
        $hasAdminSecret = $this->authService->isSecretValidForRole(User::ROLE_ADMIN, $request);

        if (!$hasAdminSecret && User::ROLE_ADMIN == $user->getRoleName()) {
            throw new RuntimeException('Admin emails not accepted.', 403);
        }

        $user->recreateRegistrationToken();

        $this->em->flush($user);

        $this->formatter->setJmsSerialiserGroups(['user']);

        return $user;
    }

    /**
     * @Route("/get-by-token/{token}", methods={"GET"})
     */
    public function getByToken(Request $request, $token)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new RuntimeException('client secret not accepted.', 403);
        }

        /* @var $user User */
        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new RuntimeException($user->getRoleName().' user role not allowed from this client.', 403);
        }

        // `user-login` contains number of clients and reports, needed to properly redirect the user to the right page after activation
        $this->formatter->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * @Route("/agree-terms-use/{token}", methods={"PUT"})
     */
    public function agreeTermsUse(Request $request, $token)
    {
        if (!$this->authService->isSecretValid($request)) {
            throw new RuntimeException('client secret not accepted.', 403);
        }

        /* @var $user User */
        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');

        if (!$this->authService->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new RuntimeException($user->getRoleName().' user role not allowed from this client.', 403);
        }

        $user->setAgreeTermsUse(true);
        if ($user->regBeforeToday($user)) {
            $user->setRegistrationToken(null);
        }

        $this->em->persist($user);
        $this->em->flush($user);

        return $user->getId();
    }

    /**
     * @Route("/{id}/team", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("is_granted('ROLE_ORG')")
     */
    public function getTeamByUserId(Request $request, $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User|null $requestedUser */
        $requestedUser = $this->userRepository->find($id);

        if (!$requestedUser) {
            throw new RuntimeException('User not found', 419);
        }

        /** @var ArrayCollection $requestedUserTeams */
        $requestedUserTeams = $requestedUser->getTeams();

        /** @var ArrayCollection $loggedInUserTeams */
        $loggedInUserTeams = $loggedInUser->getTeams();
        if ($requestedUserTeams->first() !== $loggedInUserTeams->first()) {
            throw $this->createAccessDeniedException('User not part of the same team');
        }

        $groups = $request->query->has('groups') ?
            (array) $request->query->get('groups') :
            ['team', 'team-users', 'user'];

        $this->formatter->setJmsSerialiserGroups($groups);

        return $requestedUserTeams->first();
    }

    /**
     * Endpoint for getting a reg token for user.
     *
     * @Route("/get-reg-token", methods={"GET"})
     *
     * @throws Exception
     */
    public function getRegToken(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->recreateRegistrationToken();
        $user->setRegistrationToken($user->getRegistrationToken());

        $this->em->persist($user);
        $this->em->flush();

        return $user->getRegistrationToken();
    }
}

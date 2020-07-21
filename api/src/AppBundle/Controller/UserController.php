<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\Security\UserVoter;
use AppBundle\Service\UserService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security as SecurityHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends RestController
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    /**
     * @var UserVoter
     */
    private $userVoter;

    /**
     * @var SecurityHelper
     */
    private $securityHelper;

    public function __construct(
        UserService $userService,
        EncoderFactoryInterface $encoderFactory,
        UserRepository $userRepository,
        ClientRepository $clientRepository,
        UserVoter $userVoter,
        SecurityHelper $securityHelper
    )
    {
        $this->userService = $userService;
        $this->encoderFactory = $encoderFactory;
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->userVoter = $userVoter;
        $this->securityHelper = $securityHelper;
    }

    /**
     * @Route("", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_ORG_NAMED') or has_role('ROLE_ORG_ADMIN')")
     */
    public function add(Request $request)
    {
        $data = $this->deserializeBodyContent($request, [
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
        $this->setJmsSerialiserGroups($groups);

        return $newUser;
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

        if ($loggedInUser->getId() != $requestedUser->getId()
            && !$this->isGranted(User::ROLE_ADMIN)
            && !$this->isGranted(User::ROLE_AD)
            && !$this->isGranted(User::ROLE_ORG_NAMED)
            && !$this->isGranted(User::ROLE_ORG_ADMIN)
        ) {
            throw $this->createAccessDeniedException("Non-admin not authorised to change other user's data");
        }

        /** @var User $originalUser */
        $originalUser = clone $requestedUser;

        $data = $this->deserializeBodyContent($request);
        $this->populateUser($requestedUser, $data);

        // check if rolename in data - if so add audit log
        $this->userService->editUser($originalUser, $requestedUser);

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

        $data = $this->deserializeBodyContent($request, [
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

        $data = $this->deserializeBodyContent($request, [
            'password_plain' => 'notEmpty',
        ]);

        $newPassword = $this->encoderFactory->getEncoder($requestedUser)->encodePassword($data['password_plain'], $requestedUser->getSalt());

        $requestedUser->setPassword($newPassword);

        if (array_key_exists('set_active', $data)) {
            $requestedUser->setActive($data['set_active']);
        }

        $this->getEntityManager()->flush();

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
        if ($what == 'email') {
            /** @var User|null $user */
            $user = $this->userRepository->findOneBy(['email' => strtolower($filter)]);
            if (!$user) {
                throw new \RuntimeException('User not found', 404);
            }
        } elseif ($what == 'case_number') {
            /** @var Client|null $client */
            $client = $this->clientRepository->findOneBy(['caseNumber' => $filter]);
            if (!$client) {
                throw new \RuntimeException('Client not found', 404);
            }
            if (empty($client->getUsers())) {
                throw new \RuntimeException('Client has not users', 404);
            }
            $user = $client->getUsers()[0];
        } elseif ($what == 'user_id') {
            /** @var User|null $user */
            $user = $this->userRepository->find($filter);
            if (!$user) {
                throw new \RuntimeException('User not found', 419);
            }
        } else {
            throw new \RuntimeException('wrong query', 500);
        }

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $requestedUserIsLogged = $loggedInUser->getId() == $user->getId();

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($groups);

        // only allow admins to access any user, otherwise the user can only see himself
        if (!$this->isGranted(User::ROLE_ADMIN)
            && !$this->isGranted(User::ROLE_AD)
            && !$requestedUserIsLogged) {
            throw $this->createAccessDeniedException("Not authorised to see other user's data");
        }

        return $user;
    }

    /**
     * Get user by email, and retrieve only id and team names the user belongs to.
     * Only for ROLE_PROF named and admin, when adding users to multiple teams.
     * Returns empty if user doesn't exist
     *
     * @Route("/get-team-names-by-email/{email}", methods={"GET"})
     * @Security("has_role('ROLE_ORG_NAMED') or has_role('ROLE_ORG_ADMIN')")
     */
    public function getUserTeamNames(Request $request, $email)
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        $this->setJmsSerialiserGroups(['user-id', 'team-names']);

        return $user;
    }

    /**
     * Delete user with clients.
     *
     * @Route("/{id}", methods={"DELETE"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     *
     * @param int $id
     * @return array
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

        if ($canDelete === UserVoter::ACCESS_DENIED) {
            $errMessage = sprintf("A %s cannot delete a %s", $token->getUser()->getRoleName(), $deletee->getRoleName());
            throw $this->createAccessDeniedException($errMessage);
        }

        if ($deletee->getFirstClient()) {
            $clients = $deletee->getClients();
            $this->getEntityManager()->remove($clients[0]);
        }

        $this->getEntityManager()->remove($deletee);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @Route("/get-all", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     */
    public function getAll(Request $request)
    {
        $this->setJmsSerialiserGroups(['user']);
        return $this->userRepository->findUsersByQueryParameters($request);
    }

    /**
     * Requires client secret.
     *
     * @Route("/recreate-token/{email}/{type}", defaults={"email": "none"}, requirements={
     *   "type" = "(activate|pass-reset)"
     * }, methods={"PUT"})
     */
    public function recreateToken(Request $request, $email, $type)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        /** @var User $user */
        $user = $this->findEntityBy(User::class, ['email' => strtolower($email)]);

        $hasAdminSecret = $this->getAuthService()->isSecretValidForRole(User::ROLE_ADMIN, $request);

        if (!$hasAdminSecret && $user->getRoleName() == User::ROLE_ADMIN) {
            throw new \RuntimeException('Admin emails not accepted.', 403);
        }

        $user->recreateRegistrationToken();

        $this->getEntityManager()->flush($user);

        $this->setJmsSerialiserGroups(['user']);

        return $user;
    }

    /**
     * @Route("/get-by-token/{token}", methods={"GET"})
     */
    public function getByToken(Request $request, $token)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        /* @var $user User */
        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');


        if (!$this->getAuthService()->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new \RuntimeException($user->getRoleName() . ' user role not allowed from this client.', 403);
        }

        // `user-login` contains number of clients and reports, needed to properly redirect the user to the right page after activation
        $this->setJmsSerialiserGroups(['user', 'user-login']);

        return $user;
    }

    /**
     * @Route("/agree-terms-use/{token}", methods={"PUT"})
     */
    public function agreeTermsUSe(Request $request, $token)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $user = $this->findEntityBy(User::class, ['registrationToken' => $token], 'User not found');
        /* @var $user User */

        if (!$this->getAuthService()->isSecretValidForRole($user->getRoleName(), $request)) {
            throw new \RuntimeException($user->getRoleName() . ' user role not allowed from this client.', 403);
        }

        $user->setAgreeTermsUse(true);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush($user);

        return $user->getId();
    }

    /**
     * call setters on User when $data contains values.
     * //TODO move to service
     *
     * @param User $user
     * @param array          $data
     */
    private function populateUser(User $user, array $data)
    {
        // Cannot easily(*) use JSM deserialising with already constructed objects.
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
            $user->setLastLoggedIn(new \DateTime($data['last_logged_in']));
        }

        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }

        if (!empty($data['role_name'])) {
            $roleToSet = $data['role_name'];
            $user->setRoleName($roleToSet);
        }

        return $user;
    }

    /**
     * @Route("/{id}/team", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_ORG')")
     */
    public function getTeamByUserId(Request $request, $id)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();

        /** @var User|null $requestedUser */
        $requestedUser = $this->userRepository->find($id);

        if (!$requestedUser) {
            throw new \RuntimeException('User not found', 419);
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

        $this->setJmsSerialiserGroups($groups);

        return $requestedUserTeams->first();
    }
}

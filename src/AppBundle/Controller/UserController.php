<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Doctrine\DBAL\Query\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

//TODO
//http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html

/**
 * @Route("/user")
 */
class UserController extends RestController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN, EntityDir\User::ROLE_AD]);

        $data = $this->deserializeBodyContent($request, [
            'role_name' => 'notEmpty',
            'email' => 'notEmpty',
            'firstname' => 'mustExist',
            'lastname' => 'mustExist',
        ]);

        $user = new EntityDir\User();

        $this->populateUser($user, $data);
        $user->setRegistrationDate(new \DateTime());

        /*
         * Not sure we need this check, email field is set as unique in the db. May be try catch the unique value exception
         * thrown when persist flush ?
         */
        if ($user->getEmail() && $this->getRepository(EntityDir\User::class)->findOneBy(['email' => $user->getEmail()])) {
            throw new \RuntimeException("User with email {$user->getEmail()} already exists.");
        }

        $user->recreateRegistrationToken();

        $this->persistAndFlush($user);

        return ['id' => $user->getId()];
    }

    /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user User */

        if ($this->getUser()->getId() != $user->getId()
            && !$this->isGranted(EntityDir\User::ROLE_ADMIN)
            && !$this->isGranted(EntityDir\User::ROLE_AD)
        ) {
            throw $this->createAccessDeniedException("Non-admin not authorised to change other user's data");
        }

        $data = $this->deserializeBodyContent($request);

        $this->populateUser($user, $data);

        $this->getEntityManager()->flush($user);

        return ['id' => $user->getId()];
    }

    /**
     * //TODO take user from logged user.
     *
     * @Route("/{id}/is-password-correct")
     * @Method({"POST"})
     */
    public function isPasswordCorrect(Request $request, $id)
    {
        // for both ADMIN and DEPUTY

        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user User */
        if ($this->getUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("Not authorised to check other user's password");
        }

        $data = $this->deserializeBodyContent($request, [
            'password' => 'notEmpty',
        ]);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);

        $oldPassword = $encoder->encodePassword($data['password'], $user->getSalt());
        if ($oldPassword == $user->getPassword()) {
            return true;
        }

        return false;
    }

    /**
     * change password, activate user and send remind email.
     *
     * @Route("/{id}/set-password")
     * @Method({"PUT"})
     */
    public function changePassword(Request $request, $id)
    {
        //for both admin and users

        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user EntityDir\User */
        if ($this->getUser()->getId() != $user->getId()) {
            throw $this->createAccessDeniedException("Not authorised to change other user's data");
        }

        $data = $this->deserializeBodyContent($request, [
            'password_plain' => 'notEmpty',
        ]);

        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $newPassword = $encoder->encodePassword($data['password_plain'], $user->getSalt());

        $user->setPassword($newPassword);

        if (array_key_exists('set_active', $data)) {
            $user->setActive($data['set_active']);
        }

        $this->getEntityManager()->flush();

        return $user;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $id)
    {
        return $this->getOneByFilter($request, 'user_id', $id);
    }

    /**
     * @Route("/get-one-by/{what}/{filter}", requirements={
     *   "what" = "(user_id|email|case_number)"
     * })
     * @Method({"GET"})
     */
    public function getOneByFilter(Request $request, $what, $filter)
    {
        if ($what == 'email') {
            $user = $this->getRepository(EntityDir\User::class)->findOneBy(['email' => $filter]);
            if (!$user) {
                throw new \RuntimeException('User not found', 404);
            }
        } elseif ($what == 'case_number') {
            $client = $this->getRepository(EntityDir\Client::class)->findOneBy(['caseNumber' => $filter]);
            if (!$client) {
                throw new \RuntimeException('Client not found', 404);
            }
            if (empty($client->getUsers())) {
                throw new \RuntimeException('Client has not users', 404);
            }
            $user = $client->getUsers()[0];
        } elseif ($what == 'user_id') {
            $user = $this->getRepository(EntityDir\User::class)->find($filter);
            if (!$user) {
                throw new \RuntimeException('User not found', 419);
            }
        } else {
            throw new \RuntimeException('wrong query', 500);
        }

        $requestedUserIsLogged = $this->getUser()->getId() == $user->getId();

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user', 'role'];
        $this->setJmsSerialiserGroups($groups);

        // only allow admins to access any user, otherwise the user can only see himself
        if (!$this->isGranted(EntityDir\User::ROLE_ADMIN)
            && !$this->isGranted(EntityDir\User::ROLE_AD)
            && !$requestedUserIsLogged) {
            throw $this->createAccessDeniedException("Not authorised to see other user's data");
        }

        return $user;
    }

    /**
     * Delete user with clients.
     *
     * @Route("/{id}")
     * @Method({"DELETE"})
     *
     * @param int $id
     */
    public function delete($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_ADMIN);

        $user = $this->findEntityBy(EntityDir\User::class, $id);  /* @var $user EntityDir\User */

        // delete clients
        foreach ($user->getClients() as $client) {
            if (count($client->getReports()) > 0) {
                throw new \RuntimeException('cannot delete user with reports');
            }
            $this->getEntityManager()->remove($client);
        }

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @Route("/get-all", defaults={"order_by" = "firstname", "sort_order" = "ASC"})
     * @Method({"GET"})
     */
    public function getAll(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_ADMIN, EntityDir\User::ROLE_AD]);

        $order_by  = $request->get('order_by', 'id');
        $sort_order  = strtoupper($request->get('sort_order', 'DESC'));
        $limit  = $request->get('limit', 50);
        $offset  = $request->get('offset', 0);
        $roleName  = $request->get('role_name');
        $adManaged  = $request->get('ad_managed');
        $odrEnabled  = $request->get('odr_enabled');
        $q  = $request->get('q');

        $qb = $this->getRepository(EntityDir\User::class)->createQueryBuilder('u');
        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);
        $qb->orderBy('u.'.$order_by, $sort_order);

        if ($roleName) {
            $qb->andWhere('u.roleName = :role');
            $qb->setParameter('role', $roleName);
        }

        if ($adManaged) {
            $qb->andWhere('u.adManaged = true');
        }

        if ($odrEnabled) {
            $qb->andWhere('u.odrEnabled = true');
        }

        if ($q) {
            if (preg_match('/^[0-9t]{8}$/i', $q)) { // case number
                $qb->leftJoin('u.clients', 'c');
                $qb->andWhere('lower(c.caseNumber) = :cn');
                $qb->setParameter('cn', strtolower($q));
            } else { // mail or first/lastname or user or client
                $qb->leftJoin('u.clients', 'c');
                $qb->andWhere('lower(u.email) LIKE :qLike OR lower(u.firstname) LIKE :qLike OR lower(u.lastname) LIKE :qLike OR lower(c.firstname) LIKE :qLike OR lower(c.lastname) LIKE :qLike ');
                $qb->setParameter('qLike', '%'.strtolower($q).'%');
            }
        }

        $this->setJmsSerialiserGroups(['user']);

        $users = $qb->getQuery()->getResult(); /* @var $reports Report[] */

        return $users;
        //$this->getRepository(EntityDir\User::class)->findBy($criteria, [$order_by => $sort_order], $limit, $offset);
    }

    /**
     * Requires client secret.
     *
     * @Route("/recreate-token/{email}/{type}", defaults={"email": "none"}, requirements={
     *   "type" = "(activate|pass-reset)"
     * })
     * @Method({"PUT"})
     */
    public function recreateToken(Request $request, $email, $type)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }
        $user = $this->findEntityBy(EntityDir\User::class, ['email' => $email]);

        $user->recreateRegistrationToken();

        $this->getEntityManager()->flush($user);

        $this->setJmsSerialiserGroups(['user']);

        return $user;
    }

    /**
     * @Route("/get-by-token/{token}")
     * @Method({"GET"})
     */
    public function getByToken(Request $request, $token)
    {
        if (!$this->getAuthService()->isSecretValid($request)) {
            throw new \RuntimeException('client secret not accepted.', 403);
        }

        $user = $this->findEntityBy(EntityDir\User::class, ['registrationToken' => $token], 'User not found'); /* @var $user User */

        if (!$this->getAuthService()->isSecretValidForUser($user, $request)) {
            throw new \RuntimeException($user->getRoleName() . ' user role not allowed from this client.', 403);
        }

        return $user;
    }

    /**
     * call setters on User when $data contains values.
     *
     * @param User  $user
     * @param array $data
     */
    private function populateUser(EntityDir\User $user, array $data)
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
            'odr_enabled' => 'setOdrEnabled',
            'ad_managed' => 'setAdManaged',
            'role_name' => 'setRoleName',
            'job_title' => 'setJobTitle',
        ]);

        if (array_key_exists('last_logged_in', $data)) {
            $user->setLastLoggedIn(new \DateTime($data['last_logged_in']));
        }

        if (array_key_exists('pa_team_name', $data) /*&& $user->getRoleName() == EntityDir\User::ROLE_PA*/) {
            if ($user->getTeams()->isEmpty()) {
                $user->addTeam(new EntityDir\Team());
            }
            $team = $user->getTeams()->first()->setTeamName($data['pa_team_name']);
            $this->persistAndFlush($team);
        }

        if (!empty($data['registration_token'])) {
            $user->setRegistrationToken($data['registration_token']);
        }

        if (!empty($data['token_date'])) { //important, keep this after "setRegistrationToken" otherwise date will be reset
            $user->setTokenDate(new \DateTime($data['token_date']));
        }
    }
}

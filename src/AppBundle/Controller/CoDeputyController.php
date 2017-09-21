<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use JMS\Serializer\Exception\RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/codeputy/")
 */
class CoDeputyController extends RestController
{

    /**
     * @Route("add")
     * @Method({"POST"})
     */
    public function add(Request $request)
    {
        $this->denyAccessUnlessGranted([EntityDir\User::ROLE_DEPUTY]);

        $data = $this->deserializeBodyContent($request, [
            'email' => 'notEmpty',
        ]);

        $loggedInUser = $this->getUser();
        $newUser = new EntityDir\User();

        $newUser->setFirstname('');
        $newUser->setLastname('');
        $newUser->setEmail($data['email']);
        $newUser->recreateRegistrationToken();
        $newUser->setRoleName(EntityDir\User::ROLE_LAY_DEPUTY);
        foreach ($loggedInUser->getClients() as $client) {
            $newUser->addClient($client);
        }

        $userService = $this->get('opg_digideps.user_service');

        $userService->addUser($loggedInUser, $newUser, $data);

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($groups);

        return $newUser;
    }


    /**
     * @Route("{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $user = $this->findEntityBy(EntityDir\User::class, $id, 'User not found'); /* @var $user User */

        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);
        if ( !$user->isCoDeputy()
            || !$this->getUser()->isCoDeputy()
            || ($this->getUser()->getIdOfClientWithDetails() != $user->getIdOfClientWithDetails()))
        {
            throw $this->createAccessDeniedException("User not authorised to update other user's data");
        }

        $data = $this->deserializeBodyContent($request, ['email' => 'notEmpty']);
        if (!empty($data['email'])) {
            $originalUser = clone $user;
            $user->setEmail($data['email']);
            $userService = $this->get('opg_digideps.user_service');
            $userService->editUser($originalUser, $user);
        }

        return [];
    }
}

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

        $userService = $this->get('opg_digideps.user_service');

        $userService->addUser($loggedInUser, $newUser, $data);

        $groups = $request->query->has('groups') ?
            $request->query->get('groups') : ['user'];
        $this->setJmsSerialiserGroups($groups);

        return $newUser;
    }
}

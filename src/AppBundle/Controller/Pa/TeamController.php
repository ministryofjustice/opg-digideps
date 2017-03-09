<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;
use AppBundle\Form as FormDir;

/**
 * @Route("/pa/team")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("/add-user", name="pa_team_user")
     * @Template
     */
    public function addUserAction(Request $request)
    {
        $form = $this->createForm(new FormDir\User\UserDetailsPaType());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            $this->getRestClient()->post('user/add', $formData);
            $request->getSession()->getFlashBag()->add('notice', 'User has been added');

            $redirectRoute = 'pa_team';

            return $this->redirect($this->generateUrl($redirectRoute));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

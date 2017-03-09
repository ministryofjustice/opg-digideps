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
     * @Route("/add-team-member", name="add_team_member")
     * @Template()
     */
    public function addTeamMemberAction(Request $request)
    {
        $form = $this->createForm(new FormDir\Pa\TeamMemberAccount([]));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            $this->getRestClient()->post('user/add', $formData);
            $request->getSession()->getFlashBag()->add('notice', 'Team member has been added');

            $redirectRoute = 'pa_team';

            return $this->redirect($this->generateUrl($redirectRoute));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

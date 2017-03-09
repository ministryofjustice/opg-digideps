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
        $form = $this->createForm(new FormDir\Pa\TeamMemberAccount());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $user = $this->getRestClient()->post('user', $user, ['pa_team_add'], 'User');

            // activation link
            $activationEmail = $this->getMailFactory()->createActivationEmail($user);
            $this->getMailSender()->send($activationEmail, ['text', 'html']);

            return $this->redirectToRoute('pa_team');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

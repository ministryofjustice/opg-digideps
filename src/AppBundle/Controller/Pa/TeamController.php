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
     * @Route("", name="pa_team")
     * @Template
     */
    public function listAction(Request $request)
    {
        $teamMembers = [
            $this->getUser()
        ];

        $i = 50; while ($i--) {
        $user = new EntityDir\User();
        $user->setFirstname('John'.$i);
        $user->setLastname('Red'.$i);
        $user->setRoleName('ROLE_PA_UNNAMED');
        $user->setEmail('jr'.$i.'@example.org');
        $teamMembers[] = $user;
    }

        return [
            'teamMembers' => $teamMembers
        ];
    }


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

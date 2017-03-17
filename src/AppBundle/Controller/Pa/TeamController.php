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
        $teamMembers = $this->getRestClient()->get('team/members', 'User[]');

        return [
            'teamMembers' => $teamMembers
        ];
    }


    /**
     * @Route("/add", name="add_team_member")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $form = $this->createForm(new FormDir\Pa\TeamMemberAccount(true));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $user = $this->getRestClient()->post('user', $user, ['pa_team_add'], 'User');

            $request->getSession()->getFlashBag()->add('notice', 'The user has been added');

            // activation link
            $activationEmail = $this->getMailFactory()->createActivationEmail($user);
            $this->getMailSender()->send($activationEmail, ['text', 'html']);

            return $this->redirectToRoute('pa_team');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="edit_team_member")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $user = $this->getRestClient()->get('team/member/'.$id, 'User');

        if ($this->getUser()->getRoleName() === EntityDir\User::ROLE_PA_TEAM_MEMBER) {
            throw $this->createAccessDeniedException('Team member cannot edit Team member');
        }
        if ($this->getUser()->getRoleName() !== EntityDir\User::ROLE_PA &&
            $user->getRoleName() === EntityDir\User::ROLE_PA
        ) {
            throw $this->createAccessDeniedException('Only Named PAs can edit (other) named PAs');
        }


        $showRoleNameField = $user->getRoleName() !== EntityDir\User::ROLE_PA;
        $form = $this->createForm(new FormDir\Pa\TeamMemberAccount($showRoleNameField), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();
            $this->getRestClient()->put('user/'  .$id, $user, ['pa_team_add'], 'User');

            $request->getSession()->getFlashBag()->add('notice', ' The user has been edited');

            return $this->redirectToRoute('pa_team');
        }

        return [
            'form' => $form->createView()
        ];
    }
}

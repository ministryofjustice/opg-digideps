<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Entity as EntityDir;
use AppBundle\Exception\DisplayableException;
use AppBundle\Exception\RestClientException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\AbstractController;
use AppBundle\Form as FormDir;
use Symfony\Component\HttpFoundation\Response;

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
        $this->denyAccessUnlessGranted('add-user', null, 'Access denied');

        $team = $this->getRestClient()->get('user/' .  $this->getUser()->getId() . '/team', 'Team');

        $form = $this->createForm(new FormDir\Pa\TeamMemberAccount($team, $this->getUser()));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            if (!in_array($user->getRoleName(), [EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER])) {
                $user->setRoleName(EntityDir\User::ROLE_PA_TEAM_MEMBER);
            }

            try {
                $user = $this->getRestClient()->post('user', $user, ['pa_team_add'], 'User');

                $request->getSession()->getFlashBag()->add('notice', 'The user has been added');

                // activation link
                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, ['text', 'html']);

                return $this->redirectToRoute('pa_team');

            } catch (\Exception $e) {
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $form->addError(new FormError($e->getData()['message']));
                }
                return [
                    'form' => $form->createView(),
                ];
            }
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

        $this->denyAccessUnlessGranted('edit-user', $user, 'Access denied');

        $team = $this->getRestClient()->get('user/' .  $this->getUser()->getId() . '/team', 'Team');

        $form = $this->createForm(new FormDir\Pa\TeamMemberAccount($team, $this->getUser(), $user), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            try {
                $this->getRestClient()->put('user/'  .$id, $user, ['pa_team_add'], 'User');
            } catch (\Exception $e) {
                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $form->addError(new FormError($e->getData()['message']));
                }
            }

            $request->getSession()->getFlashBag()->add('notice', ' The user has been edited');

            return $this->redirectToRoute('pa_team');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Resend activation email to pa team member
     *
     * @Route("/send-activation-link/{id}", name="team_send_activation_link")
     *
     * @param Request $request
     * @param $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resendActivationEmailAction(Request $request, $id)
    {
        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->get('team/member/'.$id, 'User');

            $user = $this->getRestClient()->userRecreateToken($user->getEmail(), 'pass-reset');
            $activationEmail = $this->getMailFactory()->createActivationEmail($user);
            $this->getMailSender()->send($activationEmail, ['text', 'html']);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'An activation email has been sent to the user.'
            );

        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());
            $request->getSession()->getFlashBag()->add(
                'error',
                'An activation email could not be sent.'
            );
        }

        return $this->redirectToRoute('pa_team');

    }

    /**
     * Confirm delete user form
     *
     * @Route("/delete-user/{id}", name="delete_team_member")
     * @Template()
     */
    public function deleteConfirmAction(Request $request, $id, $confirmed = false)
    {
        // The rest call ensures that only team members get returned and permission checks work as expected
        $user = $this->getRestClient()->get('team/member/' . $id, 'User');

        $this->denyAccessUnlessGranted('delete-user', $user, 'Access denied');

        return ['user' => $user];
    }

    /**
     * Removes a user, adds a flash message and redirects to page
     *
     * @Route("/delete-user/{id}/confirm", name="delete_team_member_confirm")
     * @Template()
     */
    public function deleteConfirmedAction(Request $request, $id)
    {
        try {

            $userToRemove = $this->getRestClient()->delete('team/delete-user/' . $id);

            $this->denyAccessUnlessGranted('delete-user', $userToRemove, 'Access denied');

            $this->getRestClient()->delete('team/delete-user/' . $userToRemove->getId());

            $request->getSession()->getFlashBag()->add(
                'notice',
                'User has been removed.'
            );

        } catch (\Exception $e) {
            $this->get('logger')->debug($e->getMessage());

            if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    'User could not be removed'
                );
            }
        }

        return $this->redirectToRoute('pa_team');
    }
}

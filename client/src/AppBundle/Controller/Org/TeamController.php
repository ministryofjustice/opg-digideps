<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\Internal\UserApi;
use AppBundle\Service\Client\RestClient;
use AppBundle\Service\Mailer\MailFactory;
use AppBundle\Service\Mailer\MailSender;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/org/settings/user-accounts")
 */
class TeamController extends AbstractController
{
    /**@var RestClient */
    private $restClient;

    /**@var MailFactory */
    private $mailFactory;

    /**@var MailSender */
    private $mailSender;

    /** @var UserApi */
    private $userApi;

    public function __construct(
        RestClient $restClient,
        MailFactory $mailFactory,
        MailSender $mailSender,
        UserApi $userApi
    ) {
        $this->restClient = $restClient;
        $this->mailFactory = $mailFactory;
        $this->mailSender = $mailSender;
        $this->userApi = $userApi;
    }

    /**
     * @Route("", name="org_team")
     * @Template("AppBundle:Org/Team:list.html.twig")
     */
    public function listAction(Request $request)
    {
        $teamMembers = $this->restClient->get('team/members', 'User[]', ['user-list']);

        return [
            'teamMembers' => $teamMembers
        ];
    }

    /**
     * @Route("/add", name="add_team_member")
     * @Template("AppBundle:Org/Team:add.html.twig")
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted('add-user', null, 'Access denied');

        $team = $this->restClient->get(
            'user/' . $this->getUser()->getId() . '/team',
            'Team',
            ['team', 'team-users']
        );
        $validationGroups = $team->canAddAdmin() ? ['org_team_add', 'org_team_role_name'] : ['org_team_add'];
        // PA also require users to have the same domain address. PROF don't as they allow cross-team members
        if ($this->getUser()->isDeputyPa()) {
            $validationGroups[] = 'email_same_domain';
        }
        $form = $this->createForm(FormDir\Org\TeamMemberAccountType::class, null, [
            'team' => $team,
            'loggedInUser' => $this->getUser(),
            'validation_groups' => $validationGroups
         ]);

        $form->handleRequest($request);

        // If the email belong to another PROF team, just add the user to the team
        // Validation is skipped
        if ($form->isSubmitted()
            && $this->getUser()->isProfNamedOrAdmin()
            && ($email = $form->getData()->getEmail())
            && ($userInfo = $this->restClient->get('user/get-team-names-by-email/' . $email, 'User'))
            && $userInfo->getTeamNames() !== null
            && count($userInfo->getTeamNames()) > 0
        ) {
            if ($userInfo->isDeputyPa()) {
                throw new \RuntimeException('User already belonging to a PA team', 422);
            }
            $this->restClient->put('team/add-to-team/' . $userInfo->getId(), []);
            $request->getSession()->getFlashBag()->add('notice', 'The user has been added to the team'); // @biggs change if needed
            return $this->redirectToRoute('org_team');
        }


        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $user EntityDir\User */
            $user = $form->getData();

            if ($this->isGranted(EntityDir\User::ROLE_PA) && !in_array($user->getRoleName(), [EntityDir\User::ROLE_PA_ADMIN, EntityDir\User::ROLE_PA_TEAM_MEMBER])) {
                $user->setRoleName(EntityDir\User::ROLE_PA_TEAM_MEMBER);
            }

            if ($this->isGranted(EntityDir\User::ROLE_PROF) && !in_array($user->getRoleName(), [EntityDir\User::ROLE_PROF_ADMIN, EntityDir\User::ROLE_PROF_TEAM_MEMBER])) {
                $user->setRoleName(EntityDir\User::ROLE_PROF_TEAM_MEMBER);
            }

            try {
                // Check user belonging to another team. If so:
                // PROF named or admin: add to all the teams the current user belongs to
                // all the other cases (PROF team member and all PAs): throw an exception


                // if the above doesn't apply: continue adding the user
                $user = $this->restClient->post('user', $user, ['org_team_add'], 'User');
                $request->getSession()->getFlashBag()->add('notice', 'The user has been added');

                $invitationEmail = $this->mailFactory->createInvitationEmail($user);
                $this->mailSender->send($invitationEmail, ['text', 'html']);

                return $this->redirectToRoute('org_team');
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'org-team')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/edit/{id}", name="edit_team_member")
     * @Template("AppBundle:Org/Team:edit.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $user = $this->restClient->get('team/member/' . $id, 'User');

        $this->denyAccessUnlessGranted('edit-user', $user, 'Access denied');

        if ($this->getUser()->getId() == $user->getId()) {
            throw $this->createNotFoundException('User cannot edit their own account at this URL');
        }

        $team = $this->restClient->get(
            'user/' . $this->getUser()->getId() . '/team',
            'Team',
            ['team', 'team-users']
        );

        $validationGroups = $team->canAddAdmin() ? ['user_details_org', 'org_team_role_name'] : ['user_details_org'];

        $form = $this->createForm(
            FormDir\Org\TeamMemberAccountType::class,
            $user,
            ['team' => $team, 'loggedInUser' => $this->getUser(), 'targetUser' => $user, 'validation_groups' => $validationGroups
                                   ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            try {
                $this->restClient->put('user/' . $id, $user, ['org_team_add']);

                if ($id == $this->getUser()->getId() && ($user->getRoles() != $this->getUser()->getRoles())) {
                    $request->getSession()->getFlashBag()->add('notice', 'For security reasons you have been logged out because you have changed your admin rights. Please log in again below');
                    $redirectRoute = 'logout';
                } else {
                    $request->getSession()->getFlashBag()->add('notice', 'The user has been edited');
                    $redirectRoute = 'org_team';
                }

                return $this->redirectToRoute($redirectRoute);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'org-team')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'user' => $user,
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
            $user = $this->restClient->get('team/member/' . $id, 'User');

            $this->userApi->inviteDeputy($user->getEmail());

            $request->getSession()->getFlashBag()->add(
                'notice',
                'An activation email has been sent to the user.'
            );
        } catch (\Throwable $e) {
            $this->get('logger')->debug($e->getMessage());
            $request->getSession()->getFlashBag()->add(
                'error',
                'An activation email could not be sent.'
            );
        }

        return $this->redirectToRoute('org_team');
    }

    /**
     * Removes a user, adds a flash message and redirects to page. Asks for confirmation first.
     *
     * @Route("/delete-user/{id}", name="delete_team_member")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     */
    public function deleteConfirmAction(Request $request, $id, $confirmed = false)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $userToRemove = $this->restClient->get('team/member/' . $id, 'User');

                $this->denyAccessUnlessGranted('delete-user', $userToRemove, 'Access denied');

                // delete the user from all the teams the logged user belongs to.
                // Also removes the user if (after the operation) won't belong to any team any longer
                $this->restClient->delete('/team/delete-membership/' . $userToRemove->getId());

                $request->getSession()->getFlashBag()->add('notice', 'User account removed');
            } catch (\Throwable $e) {
                $this->get('logger')->debug($e->getMessage());

                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $request->getSession()->getFlashBag()->add(
                        'error',
                        'User could not be removed'
                    );
                }
            }

            return $this->redirectToRoute('org_team');
        }

        // The rest call ensures that only team members get returned and permission checks work as expected
        $user = $this->restClient->get('team/member/' . $id, 'User');

        $this->denyAccessUnlessGranted('delete-user', $user, 'Access denied');

        $summary = [
            ['label' => 'deletePage.summary.fullName', 'value' => $user->getFullName()],
            ['label' => 'deletePage.summary.email', 'value' => $user->getEmail()],
            [
                'label' => 'deletePage.summary.isOrgAdministrator.label',
                'value' => 'deletePage.summary.isOrgAdministrator.' . ($user->isOrgAdministrator() ? 'yes' : 'no'),
                'format' => 'translate',
            ],
        ];

        if (count($user->getTeamNames()) >= 2) {
            $count = count($user->getTeamNames()) - 1;
            $summary[] = [
                'label' => 'deletePage.summary.otherTeams.label',
                'value' => 'deletePage.summary.otherTeams.value.' . ($count === 1 ? 'singular' : 'plural'),
                'format' => 'translate',
                'translateData' => ['%count%' => $count],
            ];
        }

        return [
            'translationDomain' => 'org-team',
            'form' => $form->createView(),
            'summary' => $summary,
            'backLink' => $this->generateUrl('org_team'),
        ];
    }
}

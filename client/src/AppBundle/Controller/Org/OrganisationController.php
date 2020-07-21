<?php declare(strict_types=1);

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Logger;
use AppBundle\Service\Time\DateTimeProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/org/settings/organisation")
 */
class OrganisationController extends AbstractController
{
    /** @var DateTimeProvider */
    private $dateTimeProvider;

    /** @var Logger */
    private $logger;

    public function __construct(DateTimeProvider $dateTimeProvider, Logger $logger)
    {
        $this->dateTimeProvider = $dateTimeProvider;
        $this->logger = $logger;
    }

    /**
     * @Route("", name="org_organisation_list")
     * @Template("AppBundle:Org/Organisation:list.html.twig")
     */
    public function listAction(Request $request)
    {
        $user = $this->getUserWithData(['user-organisations', 'organisation']);

        if (count($user->getOrganisations()) === 0) {
            throw $this->createNotFoundException();
        } elseif (count($user->getOrganisations()) === 1) {
            $organisationId = $user->getOrganisations()[0]->getId();
            return $this->redirectToRoute('org_organisation_view', ['id' => $organisationId]);
        }

        return [
            'organisations' => $user->getOrganisations(),
        ];
    }

    /**
     * @Route("/{id}", name="org_organisation_view")
     * @Template("AppBundle:Org/Organisation:view.html.twig")
     */
    public function viewAction(Request $request, string $id)
    {
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        return [
            'organisation' => $organisation
        ];
    }

    /**
     * @Route("/{id}/add-user", name="org_organisation_add_member")
     * @Template("AppBundle:Org/Organisation:add.html.twig")
     */
    public function addAction(Request $request, int $id)
    {
        $this->denyAccessUnlessGranted('add-user');

        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        } catch (AccessDeniedException $e) {
            throw ($e);
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        if ($this->isGranted(EntityDir\User::ROLE_PA)) {
            $adminRole = EntityDir\User::ROLE_PA_ADMIN;
            $memberRole = EntityDir\User::ROLE_PA_TEAM_MEMBER;
        } else {
            $adminRole = EntityDir\User::ROLE_PROF_ADMIN;
            $memberRole = EntityDir\User::ROLE_PROF_TEAM_MEMBER;
        }

        $form = $this->createForm(FormDir\Org\OrganisationMemberType::class, null, [
            'role_admin' => $adminRole,
            'role_member' => $memberRole,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $email = $form->getData()->getEmail();
                $existingUser = $this->getRestClient()->get('user/get-team-names-by-email/' . $email, 'User');

                if ($existingUser->getId()) {
                    // existing users just get added to the organisation
                    $this->getRestClient()->put('v2/organisation/' . $organisation->getId() . '/user/' . $existingUser->getId(), '');
                } else {
                    /** @var EntityDir\User $user */
                    $user = $form->getData();

                    /** @var EntityDir\User $user */
                    $user = $this->getRestClient()->post('user', $user, ['org_team_add'], 'User');

                    $invitationEmail = $this->getMailFactory()->createInvitationEmail($user);
                    $this->getMailSender()->send($invitationEmail);

                    $this->getRestClient()->put('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId(), '');
                }

                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        /** @var TranslatorInterface */
                        $translator = $this->get('translator');

                        $form->get('email')->addError(new FormError($translator->trans('form.email.existingError', [], 'org-organisation')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'organisation' => $organisation,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{orgId}/edit/{userId}", name="org_organisation_edit_member")
     * @Template("AppBundle:Org/Organisation:edit.html.twig")
     */
    public function editAction(Request $request, int $orgId, int $userId)
    {
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $orgId, 'Organisation');
            $userToEdit = $organisation->getUserById($userId);
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        if (!($userToEdit instanceof EntityDir\User)) {
            throw $this->createNotFoundException();
        }

        /** @var EntityDir\User */
        $currentUser = $this->getUser();

        if ($currentUser->getId() === $userToEdit->getId()) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('edit-user', $userToEdit);

        if ($this->isGranted(EntityDir\User::ROLE_PA)) {
            $adminRole = EntityDir\User::ROLE_PA_ADMIN;
            $memberRole = EntityDir\User::ROLE_PA_TEAM_MEMBER;
        } else {
            $adminRole = EntityDir\User::ROLE_PROF_ADMIN;
            $memberRole = EntityDir\User::ROLE_PROF_TEAM_MEMBER;
        }

        $form = $this->createForm(FormDir\Org\OrganisationMemberType::class, $userToEdit, [
            'role_admin' => $adminRole,
            'role_member' => $memberRole,
        ]);

        $oldRole = $userToEdit->getRoleName();
        $form->handleRequest($request);

        $a = '';
        if ($form->isSubmitted() && $form->isValid()) {
            $editedUser = $form->getData();
            $newRole = $editedUser->getRoleName();

            try {
                $this->getRestClient()->put('user/' . $editedUser->getId(), $editedUser, ['org_team_add']);

                $event = (new AuditEvents($this->dateTimeProvider))
                    ->roleChanged(
                        AuditEvents::TRIGGER_DEPUTY_USER,
                        $oldRole,
                        $newRole,
                        $currentUser->getEmail(),
                        $editedUser->getEmail()
                    );

                $this->logger->notice('', $event);

                $this->addFlash('notice', 'The user has been edited');

                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        /** @var TranslatorInterface */
                        $translator = $this->get('translator');
                        $form->get('email')->addError(new FormError($translator->trans('form.email.existingError', [], 'org-organisation')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'organisation' => $organisation,
            'user' => $userToEdit,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/{orgId}/delete-user/{userId}", name="org_organisation_delete_member")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     */
    public function deleteConfirmAction(Request $request, int $orgId, int $userId)
    {
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $orgId, 'Organisation');
            $user = $organisation->getUserById($userId);
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        if (!($user instanceof EntityDir\User)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('delete-user', $user, 'Access denied');

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->getRestClient()->delete('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId());

                $this->addFlash('notice', 'User account removed from organisation');
            } catch (\Throwable $e) {
                /** @var LoggerInterface */
                $logger = $this->get('logger');
                $logger->debug($e->getMessage());

                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $this->addFlash(
                        'error',
                        'User could not be removed'
                    );
                }
            }

            return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
        }

        return [
            'translationDomain' => 'org-organisation',
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.fullName', 'value' => $user->getFullName()],
                ['label' => 'deletePage.summary.email', 'value' => $user->getEmail()],
            ],
            'backLink' => $this->generateUrl('org_organisation_view', ['id' => $organisation->getId()]),
        ];
    }

    /**
     * @Route("/{orgId}/send-activation-link/{userId}", name="org_organisation_send_activation_link")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resendActivationEmailAction(Request $request, int $orgId, int $userId)
    {
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $orgId, 'Organisation');
            $user = $organisation->getUserById($userId);
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        try {
            /* @var $user EntityDir\User */
            $user = $this->getRestClient()->userRecreateToken($user->getEmail(), 'pass-reset');

            $invitationEmail = $this->getMailFactory()->createInvitationEmail($user);
            $this->getMailSender()->send($invitationEmail);

            $this->addFlash(
                'notice',
                'An activation email has been sent to the user.'
            );
        } catch (\Throwable $e) {
            /** @var LoggerInterface */
            $logger = $this->get('logger');
            $logger->debug($e->getMessage());

            $this->addFlash(
                'error',
                'An activation email could not be sent.'
            );
        }

        return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
    }
}

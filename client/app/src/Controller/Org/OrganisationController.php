<?php

declare(strict_types=1);

namespace App\Controller\Org;

use App\Controller\AbstractController;
use App\Entity\Organisation;
use App\Entity\User;
use App\Exception\RestClientException;
use App\Form\ConfirmDeleteType;
use App\Form\Org\OrganisationMemberType;
use App\Form\User\SearchUserType;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\OrganisationApi;
use App\Service\Client\Internal\UserApi;
use App\Service\Client\RestClient;
use App\Service\Logger;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '/org/settings/organisation')]
class OrganisationController extends AbstractController
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UserApi $userApi,
        private readonly RestClient $restClient,
        private readonly OrganisationApi $organisationApi,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '', name: 'org_organisation_list')]
    #[Template('@App/Org/Organisation/list.html.twig')]
    public function listAction(): RedirectResponse|array
    {
        $user = $this->userApi->getUserWithData(['user-organisations', 'organisation']);

        if (0 === count($user->getOrganisations())) {
            throw $this->createNotFoundException();
        } elseif (1 === count($user->getOrganisations())) {
            $organisationId = $user->getOrganisations()[0]->getId();

            return $this->redirectToRoute('org_organisation_view', ['id' => $organisationId]);
        }

        return [
            'organisations' => $user->getOrganisations(),
        ];
    }

    #[Route(path: '/{id}', name: 'org_organisation_view')]
    #[Template('@App/Org/Organisation/view.html.twig')]
    public function viewAction(Request $request, int $id): array
    {
        /** @var Organisation $organisation */
        $organisation = $this->restClient->get('v2/organisation/' . $id, 'Organisation');

        $currentFilters = self::getFiltersFromRequest($request);

        $form = $this->createForm(SearchUserType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $currentFilters = $form->getData() + $currentFilters;
        }

        $result = $this->restClient->get('/v2/organisation/' . $id . '/users?' . http_build_query($currentFilters), 'array');

        $users = $this->restClient->arrayToEntities(User::class . '[]', $result['records']);

        return [
            'filters' => $currentFilters,
            'organisation' => $organisation,
            'orgId' => $organisation->getId(),
            'users' => $users,
            'count' => $result['count'],
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/add-user', name: 'org_organisation_add_member')]
    #[Template('@App/Org/Organisation/add.html.twig')]
    public function addAction(Request $request, int $id): RedirectResponse|array
    {
        $this->denyAccessUnlessGranted('can-add-user');

        try {
            $organisation = $this->restClient->get('v2/organisation/' . $id, 'Organisation');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        if ($this->isGranted(User::ROLE_PA)) {
            $adminRole = User::ROLE_PA_ADMIN;
            $memberRole = User::ROLE_PA_TEAM_MEMBER;
        } else {
            $adminRole = User::ROLE_PROF_ADMIN;
            $memberRole = User::ROLE_PROF_TEAM_MEMBER;
        }

        $form = $this->createForm(OrganisationMemberType::class, null, [
            'role_admin' => $adminRole,
            'role_member' => $memberRole,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $email = $form->getData()->getEmail();
                $user = $this->userApi->getByEmailOrgAdmins($email);

                $this->denyAccessUnlessGranted('add-user', $user);

                if (!$user->getId()) {
                    /** @var User $formData */
                    $formData = $form->getData();

                    /** @var User $user */
                    $user = $this->userApi->createOrgUser($formData);
                }

                /** @var User $currentUser */
                $currentUser = $this->getUser();

                $this->organisationApi->addUserToOrganisation(
                    $organisation,
                    $user,
                    $currentUser,
                    AuditEvents::TRIGGER_ORG_USER_MANAGE_ORG_MEMBER
                );

                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        /** @var TranslatorInterface */
                        $translator = $this->translator;

                        $form->get('email')->addError(new FormError($translator->trans('form.email.existingError', [], 'org-organisation')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'organisation' => $organisation,
            'form' => $form->createView(),
        ];
    }

    /**
     * @throws \Throwable
     */
    #[Route(path: '/{orgId}/edit/{userId}', name: 'org_organisation_edit_member')]
    #[Template('@App/Org/Organisation/edit.html.twig')]
    public function editAction(Request $request, int $orgId, int $userId): RedirectResponse|array
    {
        try {
            $organisation = $this->restClient->get('v2/organisation/' . $orgId, 'Organisation');
            $userToEdit = $this->restClient->get('user/' . $userId, 'User');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        if (!($userToEdit instanceof User)) {
            throw $this->createNotFoundException();
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser->getId() === $userToEdit->getId()) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('edit-user', $userToEdit);

        if ($this->isGranted(User::ROLE_PA)) {
            $adminRole = User::ROLE_PA_ADMIN;
            $memberRole = User::ROLE_PA_TEAM_MEMBER;
        } else {
            $adminRole = User::ROLE_PROF_ADMIN;
            $memberRole = User::ROLE_PROF_TEAM_MEMBER;
        }

        $form = $this->createForm(OrganisationMemberType::class, clone $userToEdit, [
            'role_admin' => $adminRole,
            'role_member' => $memberRole,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $editedUser = $form->getData();

            try {
                $this->userApi->update($userToEdit, $editedUser, AuditEvents::TRIGGER_DEPUTY_USER_EDIT, ['org_team_add']);
                $this->addFlash('notice', 'The user has been edited');

                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        /** @var TranslatorInterface $translator */
                        $translator = $this->translator;

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
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{orgId}/delete-user/{userId}', name: 'org_organisation_delete_member')]
    #[Template('@App/Common/confirmDelete.html.twig')]
    public function deleteConfirmAction(Request $request, int $orgId, int $userId): RedirectResponse|array
    {
        try {
            $organisation = $this->restClient->get('v2/organisation/' . $orgId, 'Organisation');
            $userToRemove = $this->restClient->get('user/' . $userId, 'User');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        if (!($userToRemove instanceof User)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('delete-user', $userToRemove, 'Access denied');

        $form = $this->createForm(ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                /** @var User $currentUser */
                $currentUser = $this->getUser();

                $this->organisationApi->removeUserFromOrganisation(
                    $organisation,
                    $userToRemove,
                    $currentUser,
                    AuditEvents::TRIGGER_ORG_USER_MANAGE_ORG_MEMBER
                );

                $this->addFlash('notice', 'User account removed from organisation');
            } catch (\Throwable $e) {
                $this->logger->debug($e->getMessage());

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
                ['label' => 'deletePage.summary.fullName', 'value' => $userToRemove->getFullName()],
                ['label' => 'deletePage.summary.email', 'value' => $userToRemove->getEmail()],
            ],
            'backLink' => $this->generateUrl('org_organisation_view', ['id' => $organisation->getId()]),
        ];
    }

    #[Route(path: '/{orgId}/send-activation-link/{userId}', name: 'org_organisation_send_activation_link')]
    public function resendActivationEmailAction(int $orgId, int $userId): RedirectResponse
    {
        try {
            $organisation = $this->restClient->get('v2/organisation/' . $orgId, 'Organisation');
            $user = $organisation->getUserById($userId);
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        try {
            $this->userApi->reInviteDeputy($user->getEmail());

            $this->addFlash(
                'notice',
                'An activation email has been sent to the user.'
            );
        } catch (\Throwable $e) {
            $this->logger->debug($e->getMessage());

            $this->addFlash(
                'error',
                'An activation email could not be sent.'
            );
        }

        return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
    }

    private static function getFiltersFromRequest(Request $request): array
    {
        return [
            'q' => $request->query->get('q') ?: '',
            'limit' => $request->query->get('limit') ?: 15,
            'offset' => $request->query->get('offset') ?: 0,
        ];
    }
}

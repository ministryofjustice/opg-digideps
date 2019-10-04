<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/org/settings/organisation")
 */
class OrganisationController extends AbstractController
{
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
    public function viewAction(Request $request, int $id)
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
        try {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        } catch (RestClientException $e) {
            throw $this->createNotFoundException('Organisation not found');
        }

        $form = $this->createForm(FormDir\Org\OrganisationMemberType::class);

        $form->handleRequest($request);

        // If the email belong to a prof user, just add the user to the team
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()->getEmail();
            try {
                $user = $this->getRestClient()->get('user/get-team-names-by-email/' . $email, 'User');

                if ($user->getId()) {
                    $this->getRestClient()->put('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId(), '');
                    return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
                }
            } catch (\Throwable $e) {

            }
        }

        if ($form->isValid()) {
            /** @var $user EntityDir\User */
            $user = $form->getData();

            if ($this->isGranted(EntityDir\User::ROLE_PA)) {
                $user->setRoleName(EntityDir\User::ROLE_PA_ADMIN);
            }

            if ($this->isGranted(EntityDir\User::ROLE_PROF)) {
                $user->setRoleName(EntityDir\User::ROLE_PROF_ADMIN);
            }

            try {
                $user = $this->getRestClient()->post('user', $user, ['org_team_add'], 'User');
                $this->getRestClient()->put('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId(), '');

                $activationEmail = $this->getMailFactory()->createActivationEmail($user);
                $this->getMailSender()->send($activationEmail, ['text', 'html']);

                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'org-organisation')));
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
     * @Template("AppBundle:Org/Team:edit.html.twig")
     */
    public function editAction(Request $request, int $orgId, int $userId)
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

        if ($this->getUser()->getId() === $user->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(FormDir\Org\OrganisationMemberType::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $form->getData();

            try {
                $this->getRestClient()->put('user/' . $user->getId(), $user, ['org_team_add'], 'User');

                $request->getSession()->getFlashBag()->add('notice', 'The user has been edited');
                return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
            } catch (\Throwable $e) {
                switch ((int) $e->getCode()) {
                    case 422:
                        $form->get('email')->addError(new FormError($this->get('translator')->trans('form.email.existingError', [], 'org-organisation')));
                        break;

                    default:
                        throw $e;
                }
            }
        }

        return [
            'organisation' => $organisation,
            'user' => $user,
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

        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $this->getRestClient()->delete('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId(), '');

                $request->getSession()->getFlashBag()->add('notice', 'User account removed from organisation');
            } catch (\Throwable $e) {
                $this->get('logger')->debug($e->getMessage());

                if ($e instanceof RestClientException && isset($e->getData()['message'])) {
                    $request->getSession()->getFlashBag()->add(
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
     * @Route("{orgId}/send-activation-link/{userId}", name="org_organisation_send_activation_link")
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
            $activationEmail = $this->getMailFactory()->createActivationEmail($user);
            $this->getMailSender()->send($activationEmail, ['text', 'html']);

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

        return $this->redirectToRoute('org_organisation_view', ['id' => $organisation->getId()]);
    }
}

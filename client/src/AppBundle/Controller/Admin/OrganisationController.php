<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

/**
 * @Route("/admin/organisations")
 */
class OrganisationController extends AbstractController
{
    /**
     * @Route("/", name="admin_organisation_homepage")
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Admin/Organisation:index.html.twig")
     */
    public function indexAction()
    {
        $organisations = $this->getRestClient()->get('v2/organisation/list', 'Organisation[]');

        return [
            'organisations' => $organisations
        ];
    }

    /**
     * @Route("/{id}", name="admin_organisation_view", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Admin/Organisation:view.html.twig")
     */
    public function viewAction(Request $request, $id)
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
     * @Route("/add", name="admin_organisation_add")
     * @Route("/{id}/edit", name="admin_organisation_edit", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Admin/Organisation:form.html.twig")
     */
    public function formAction(Request $request, $id = null)
    {
        if (is_null($id)) {
            $organisation = new Organisation();
        } else {
            $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        }

        $form = $this->createForm(
            FormDir\Admin\OrganisationType::class,
            $organisation
        );

        if (!is_null($organisation->getId())) {
            if ($organisation->getIsDomainIdentifier()) {
                $form->get('emailIdentifierType')->setData('domain');
                $form->get('emailDomain')->setData($organisation->getEmailIdentifier());
            } else {
                $form->get('emailIdentifierType')->setData('address');
                $form->get('emailAddress')->setData($organisation->getEmailIdentifier());
            }
        }

        $form->handleRequest($request);

        if ($form->isValid()) {
            $organisation = $form->getData();

            try {
                if (is_null($organisation->getId())) {
                    $this->getRestClient()->post('v2/organisation', $organisation);
                    $request->getSession()->getFlashBag()->add('notice', 'The organisation has been created');
                } else {
                    $this->getRestClient()->put('v2/organisation/' . $organisation->getId(), $organisation);
                    $request->getSession()->getFlashBag()->add('notice', 'The organisation has been updated');
                }

                return $this->redirectToRoute('admin_organisation_homepage');
            } catch (RestClientException $e) {
                $form->addError(new FormError($e->getData()['message']));
            }
        }

        return [
            'form'  => $form->createView(),
            'organisation' => $organisation,
            'isEditView' => !!$organisation->getId(),
            'backLink' => $this->generateUrl('admin_organisation_homepage')
        ];
    }

    /**
     * @Route("/{id}/delete", name="admin_organisation_delete", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');

        if ($form->isValid()) {
            try {
                $this->getRestClient()->delete('v2/organisation/' . $organisation->getId());
                $request->getSession()->getFlashBag()->add('notice', 'The organisation has been removed');
            } catch (\Throwable $e) {
                $this->get('logger')->error($e->getMessage());
                $request->getSession()->getFlashBag()->add('error', 'Organisation could not be removed');
            }

            return $this->redirectToRoute('admin_organisation_homepage');
        }

        return [
            'translationDomain' => 'admin-organisations',
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.name', 'value' => $organisation->getName()],
                ['label' => 'deletePage.summary.emailIdentifier', 'value' => $organisation->getEmailIdentifierDisplay()],
                [
                    'label' => 'deletePage.summary.active.label',
                    'value' => 'deletePage.summary.active.' . ($organisation->getIsActivated() ? 'yes' : 'no'),
                    'format' => 'translate',
                ],
            ],
            'backLink' => $this->generateUrl('admin_organisation_homepage')
        ];
    }

    /**
     * @Route("/{id}/add-user", name="admin_organisation_member_add", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Admin/Organisation:add-user.html.twig")
     */
    public function addUserAction(Request $request, $id) {
        $form = $this->createForm(FormDir\Admin\OrganisationAddUserType::class);
        $form->handleRequest($request);

        $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');

        if ($form->get('email')->getData()) {
            try {
                $email = $form->get('email')->getData();
                $user = $this->getRestClient()->get('user/get-one-by/email/' . $email, 'User');

                if (!$user->isDeputyOrg()) {
                    $error = 'form.email.notOrgUserError';
                }

                if ($organisation->hasUser($user)) {
                    $error = 'form.email.alreadyInOrgError';
                }
            } catch (RestClientException $e) {
                $error = 'form.email.notFoundError';
            }
        }

        if (isset($error)) {
            $errorMessage = $this->get('translator')->trans($error, [], 'admin-organisation-users');
            $form->get('email')->addError(new FormError($errorMessage));
            $user = new User();
        }

        if ($form->get('confirm')->isClicked()) {
            $this->getRestClient()->put('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId(), '');
            $request->getSession()->getFlashBag()->add('notice', $user->getFullName() . ' has been added to ' . $organisation->getName());

            return$this->redirectToRoute('admin_organisation_view', ['id' => $organisation->getId()]);
        }

        return [
            'form' => $form->createView(),
            'organisation' => $organisation,
            'user' => isset($user) ? $user : new User(),
            'backLink' => $this->generateUrl('admin_organisation_view', ['id' => $organisation->getId()])
        ];
    }

    /**
     * @Route("/{id}/delete-user/{userId}", name="admin_organisation_member_delete", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_ADMIN')")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     */
    public function deleteUserAction(Request $request, $id, $userId) {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $organisation = $this->getRestClient()->get('v2/organisation/' . $id, 'Organisation');
        $user = $this->getRestClient()->get('user/' . $userId, 'User');

        if ($form->isValid()) {
            try {
                $this->getRestClient()->delete('v2/organisation/' . $organisation->getId() . '/user/' . $user->getId());
                $request->getSession()->getFlashBag()->add('notice', 'User has been removed from ' . $organisation->getName());
            } catch (\Throwable $e) {
                $this->get('logger')->error($e->getMessage());
                $request->getSession()->getFlashBag()->add('error', 'User could not be removed form '  . $organisation->getName());
            }

            return $this->redirectToRoute('admin_organisation_view', ['id' => $organisation->getId()]);
        }

        return [
            'translationDomain' => 'admin-organisation-users',
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.organisationName', 'value' => $organisation->getName()],
                ['label' => 'deletePage.summary.userName', 'value' => $user->getFullName()],
                ['label' => 'deletePage.summary.userEmail', 'value' => $user->getEmail()],
            ],
            'backLink' => $this->generateUrl('admin_organisation_view', ['id' => $organisation->getId()])
        ];
    }
}

<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Organisation as Organisation;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

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
        $endpoint = 'setting/service-notification';
        $organisations = $this->getRestClient()->get('v2/organisation/list', 'Organisation[]');

        return [
            'organisations' => $organisations
        ];
    }

    /**
     * @Route("/add", name="admin_organisation_add")
     * @Route("/edit/{id}", name="admin_organisation_edit")
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

            if (is_null($organisation->getId())) {
                $this->getRestClient()->post('v2/organisation', $organisation);
                $request->getSession()->getFlashBag()->add('notice', 'The organisation has been created');
            } else {
                $this->getRestClient()->put('v2/organisation/' . $organisation->getId(), $organisation);
                $request->getSession()->getFlashBag()->add('notice', 'The organisation has been updated');
            }

            return $this->redirectToRoute('admin_organisation_homepage');
        }

        return [
            'form'  => $form->createView(),
            'organisation' => $organisation,
            'isEditView' => !!$organisation->getId(),
            'backLink' => $this->generateUrl('admin_organisation_homepage')
        ];
    }

    /**
     * @Route("/delete/{id}", name="admin_organisation_delete")
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
}

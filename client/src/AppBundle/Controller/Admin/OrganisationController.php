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
 * @Route("/admin/organisation")
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
        $organisations = $this->getRestClient()->get('v2/organisation/list', 'Organisation');

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
    public function formAction(Request $request, $id)
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

        $form->handleRequest($request);

        if ($form->isValid()) {
            $organisation = $form->getData();

            if (is_null($id)) {
                $this->getRestClient()->post('v2/organisation', $organisation);
                $request->getSession()->getFlashBag()->add('notice', 'The organisation has been created');
            } else {
                $this->getRestClient()->put('v2/organisation/' . $organistion->getId(), $organisation);
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
}

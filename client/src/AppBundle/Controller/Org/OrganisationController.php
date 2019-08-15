<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception\RestClientException;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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

        if (count($user->getOrganisations()) === 1) {
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
}

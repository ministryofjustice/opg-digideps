<?php

namespace AppBundle\Controller\Org;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/org")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="org_dashboard")
     * @Template("AppBundle:Org/Index:dashboard.html.twig")
     */
    public function dashboardAction(Request $request)
    {
        $currentFilters = [
            'q'                 => $request->get('q'),
            'status'            => $request->get('status'),
            'exclude_submitted' => true,
            'sort'              => 'end_date',
            'sort_direction'    => 'asc',
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
        ];

        $endpoint = sprintf(
            '%s?%s',
            $this->getUser()->belongsToActiveOrganisation() ?'/report/get-all-by-org' : 'report/get-all-by-user',
            http_build_query($currentFilters)
        );

        $response = $this->getRestClient()->get($endpoint, 'array');

        $reports = $this->getRestClient()->arrayToEntities(EntityDir\Report\Report::class . '[]', $response['reports']);

        return [
            'filters' => $currentFilters,
            'reports' => $reports,
            'counts'  => [
                'total' => $response['counts']['total'],
                'notStarted' => $response['counts']['notStarted'],
                'notFinished' => $response['counts']['notFinished'],
                'readyToSubmit' => $response['counts']['readyToSubmit'],
            ],
        ];
    }

    /**
     * Client edit page
     * Report is only associated to one client, and it's needed for back link routing,
     * so it's retrieved with the report with a single API call
     *
     * @Route("/client/{clientId}/edit", name="org_client_edit")
     * @Template("AppBundle:Org/Index:clientEdit.html.twig")
     */
    public function clientEditAction(Request $request, $clientId)
    {
        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);
        // PA client profile is ATM relying on report ID, this is a working until next refactor

        $returnLink = ($request->get('from') === 'declaration') ?
            $this->generateUrl('report_declaration', ['reportId' => $client->getCurrentReport()->getId()]) :
            $this->generateUrl('report_overview', ['reportId'=>$client->getCurrentReport()->getId()]);

        $form = $this->createForm(FormDir\Org\ClientType::class, $client);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, ['pa-edit']);
            $request->getSession()->getFlashBag()->add('notice', 'The client details have been edited');

            return $this->redirect($returnLink);
        }

        return [
            'backLink' => $returnLink,
            'form' => $form->createView(),
            'client'=>$client,
        ];
    }

    /**
     * Client archive page
     *
     * @Route("/client/{clientId}/archive", name="org_client_archive")
     * @Template("AppBundle:Org/Index:clientArchive.html.twig")
     */
    public function clientArchiveAction(Request $request, $clientId)
    {
        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'current-report']);
        // PA client profile is ATM relying on report ID, this is a working until next refactor
        $returnLink = $this->generateUrl('report_overview', ['reportId'=>$client->getCurrentReport()->getId()]);
        $form = $this->createForm(FormDir\Org\ClientArchiveType::class, $client);
        $form->handleRequest($request);

        // edit client form
        if ($form->get('save')->isClicked() && $form->isValid()) {
            if (true === $form->get('confirmArchive')->getData()) {
                $this->getRestClient()->apiCall('put', 'client/' . $client->getId() . '/archive', null, 'array');
                $request->getSession()->getFlashBag()->add('notice', 'The client has been archived');
                return $this->redirectToRoute('org_dashboard');
            } else {
                $form->get('confirmArchive')->addError(new FormError($this->get('translator')->trans('form.error.confirmArchive', [], 'pa-client-archive')));
            }
        }

        return [
            'backLink' => $returnLink,
            'form' => $form->createView(),
            'client'=>$client,
        ];
    }
}

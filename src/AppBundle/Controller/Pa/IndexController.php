<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/pa")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="pa_dashboard")
     * @Template
     */
    public function dashboardAction(Request $request)
    {
        $user = $this->getUser();

        $currentFilters = [
            'q'                 => $request->get('q'),
            'status'            => $request->get('status'),
            'exclude_submitted' => true,
            'sort'              => 'end_date',
            'sort_direction'    => 'asc',
            'limit'             => $request->query->get('limit') ?: 15,
            'offset'            => $request->query->get('offset') ?: 0,
        ];

        $ret = $this->getRestClient()->get('/report/get-all?' . http_build_query($currentFilters), 'array', ['client', 'report', 'status']);
        /* @var $clients EntityDir\Client[] */
        $reports = $this->get('restClient')->arrayToEntities(EntityDir\Report\Report::class . '[]', $ret['reports']);

        return [
            'filters' => $currentFilters,
            'reports' => $reports,
            'counts'  => [
                'total'         => $ret['counts']['total'],
                'notStarted'    => $ret['counts']['notStarted'],
                'notFinished'   => $ret['counts']['notFinished'],
                'readyToSubmit' => $ret['counts']['readyToSubmit'],
            ],
        ];
    }

    /**
     * Client edit page
     * Report is only associated to one client, and it's needed for back link routing,
     * so it's retrieved with the report with a single API call
     *
     * @Route("/client/{clientId}/edit", name="pa_client_edit")
     * @Template
     */
    public function clientEditAction(Request $request, $clientId)
    {
        /** @var $client EntityDir\Client */
        $client = $this->getRestClient()->get('client/' . $clientId, 'Client', ['client', 'report-id', 'report-current']);
        // PA client profile is ATM relying on report ID, this is a working until next refactor
        $returnLink = $this->generateUrl('report_overview', ['reportId'=>$client->getReportCurrent()->getId()]);
        $form = $this->createForm(new FormDir\Pa\ClientType(), $client);
        $form->handleRequest($request);

        // edit client form
        if ($form->isValid()) {
            $clientUpdated = $form->getData();
            $clientUpdated->setId($client->getId());
            $this->getRestClient()->put('client/upsert', $clientUpdated, ['pa-edit']);
            $request->getSession()->getFlashBag()->add('notice', "The demographics have been edited");

            return $this->redirect($returnLink);
        }

        return [
            'backLink' => $returnLink,
            'form' => $form->createView(),
            'client'=>$client,
        ];
    }

    /**
     * @Route("/settings", name="pa_settings")
     * @Template
     */
    public function settingsAction(Request $request)
    {
        return [];
    }
}

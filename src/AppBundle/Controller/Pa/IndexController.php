<?php

namespace AppBundle\Controller\Pa;

use AppBundle\Entity as EntityDir;
use AppBundle\Controller\AbstractController;
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
            'homePageHeaderLink' => $this->generateUrl('pa_dashboard')
        ];
    }

    /**
     * @Route("/settings", name="pa_settings")
     * @Template
     */
    public function settingsAction(Request $request)
    {
        return [
            'homePageHeaderLink' => $this->generateUrl('pa_dashboard')
        ];
    }


}

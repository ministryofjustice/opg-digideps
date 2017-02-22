<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/pa")
 */
class PaController extends AbstractController
{
    /**
     * @Route("/", name="pa_dashboard")
     * @Template
     */
    public function indexAction(Request $request)
    {
        $currentFilters = [
            //'q'       => '123 sdf',
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
            'filters' => [
                'status' => $currentFilters['status'],
                'limit'  => $currentFilters['limit'],
                'offset' => $currentFilters['offset'],
            ],
            'reports' => $reports,
            'counts'  => [
                'total'         => $ret['counts']['total'],
                'notStarted'    => $ret['counts']['notStarted'],
                'notFinished'   => $ret['counts']['notFinished'],
                'readyToSubmit' => $ret['counts']['readyToSubmit'],
            ],
        ];
    }
}

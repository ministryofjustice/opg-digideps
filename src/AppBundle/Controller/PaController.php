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
     * @Route("/settings", name="pa_settings")
     * @Template
     */
    public function settingsAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/team", name="pa_team")
     * @Template
     */
    public function teamAction(Request $request)
    {
        $teamMembers = [
            $this->getUser()
        ];

        $i = 50; while ($i--) {
            $user = new EntityDir\User();
            $user->setFirstname('John'.$i);
            $user->setLastname('Red'.$i);
            $user->setRoleName('ROLE_PA_UNNAMED');
            $user->setEmail('jr'.$i.'@example.org');
            $teamMembers[] = $user;
        }

        return [
            'teamMembers' => $teamMembers
        ];
    }
}

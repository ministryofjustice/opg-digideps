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
        /* @var $clients EntityDir\Client[] */
        $endpoint = '/client/get-all?' . http_build_query([
//            'user_id' => $this->getUser()->getId(),
            'q'       => '123 sdf',
            'page'    => 1,
            'status'  => '*', // starte
        ]);

        $clients = $this->getRestClient()->get($endpoint, 'Client[]', ['client', 'report']);

        // the view needs reports data, so easier to re-organize by reports
        // note: for PA (so far), one client only has one report. And there are no clients without report
        $reports = [];
        foreach ($clients as $client) {
            $report = $client->getReports()[0]; // no reason why data is wrong
            $report->setClient($client);
            $reports[] = $report;
        }

        return [
            'reports' => $reports,
            'counts' => [
                'total' => count($reports),
                'notStarted' => 8,
                'notCompleted' => 7,
                'readyToSubmit' => 3,
            ]
        ];
    }
}

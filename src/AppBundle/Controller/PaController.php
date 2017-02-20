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

        $clients = $this->getRestClient()->get($endpoint, 'Client[]', ['client', 'report', 'status']);

        // the view needs reports data, so easier to re-organize by reports
        // note: for PA (so far), one client only has one report. And there are no clients without report\
        /* @var $reports EntityDir\Report\Report[] */
        $reports = [];
        foreach ($clients as $client) {
            $report = $client->getReports()[0]; // no reason why data is wrong
            $report->setClient($client);
            $reports[] = $report;
        }

        // calculate count
        $statesCount = ['total'=>0,'notStarted' => 0, 'readyToSubmit' => 0, 'notFinished' => 0];
        foreach($reports as $report) {
            $statesCount[$report->getStatus()->getStatus()]++;
            $statesCount['total']++;
        }

        //apply tab filter //TODO move to API to optimise if needed
        if ($filterStatus = $request->get('status')) {
            $reports = array_filter($reports, function($report) use ($filterStatus) {
                return $report->getStatus()->getStatus() == $filterStatus;
            });
        }

        return [
            'filters' => [
                'status' => $filterStatus
            ],
            'reports' => $reports,
            'counts' => [
                'total' => $statesCount['total'],
                'notStarted' => $statesCount['notStarted'],
                'notFinished' => $statesCount['notFinished'],
                'readyToSubmit' => $statesCount['readyToSubmit'],
            ]
        ];
    }
}

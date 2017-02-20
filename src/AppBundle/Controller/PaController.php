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
        $reports = [];
        $i = 100;
        while ($i--) {
            $report = new EntityDir\Report\Report();
            $client = new EntityDir\Client();
            $client->setFirstname("John $i");
            $client->setLastname("Smith $i");
            $client->setCaseNumber("190993$i");
            $report->setClient($client);
            $reports[] = $report;
        }

        return [
            'reports' => $reports,
        ];
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Service\StatsService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/stats")
 */
class StatsController extends RestController
{
    /**
     * Return CSV file created on the fly
     *
     * @Route("/stats.csv")
     * @Method({"GET"})
     */
    public function statsCsv(Request $request)
    {
        // create CSV if not added by the cron, or the "regenerated" is added
        if (!file_exists(EntityDir\CasRec::STATS_FILE_PATH) || $request->get('regenerate')) {
            $this->get('stats_service')->saveCsv(EntityDir\CasRec::STATS_FILE_PATH);
        }

        $response = new Response();
        $response->setContent(readfile(EntityDir\CasRec::STATS_FILE_PATH));

        return $response;
    }
}

<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Exception\DisplayableException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/stats")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("", name="admin_stats")
     * @Template
     */
    public function statsAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/dd-stats.csv", name="admin_stats_csv")
     * @Template
     */
    public function statsCsvAction(Request $request)
    {
        try {
            $regenerate = $request->get('regenerate') ? 1 : 0;
            $rawCsv = (string) $this->getRestClient()->get("casrec/stats.csv?regenerate=$regenerate", 'raw');
        } catch (\Exception $e) {
            throw new DisplayableException($e);
        }
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', 'plain/text');
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="dd-stats.' . date('Y-m-d') . '.csv";');
        $response->sendHeaders();
        $response->setContent($rawCsv);

        return $response;
    }
}

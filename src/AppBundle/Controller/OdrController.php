<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\ReportStatusService;
use Doctrine\Common\Util\Debug;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class OdrController extends AbstractController
{
    /**
     * @Route("/odr/overview", name="odr_overview")
     * @Template("AppBundle:Odr:overview.html.twig")
     */
    public function overviewAction()
    {
        $report = new EntityDir\Report();
        $clients = $this->getUser()->getClients();
        $client = !empty($clients) ? $clients[0] : null;
        $report->setClient($client);
        $report->setId(1);

        if ($report->getSubmitted()) {
            throw new \RuntimeException('Report already submitted and not editable.');
        }
        $reportStatusService = new ReportStatusService($report);

        return [
            'report' => $report,
            'reportStatus' => $reportStatusService,
        ];
    }
}

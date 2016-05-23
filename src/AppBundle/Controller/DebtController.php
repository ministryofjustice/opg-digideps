<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route("/report")
 */
class DebtController extends AbstractController
{
    /**
     * List debts
     *
     * @Route("/{reportId}/debts", name="debts")
     * @Template("AppBundle:Debt:list.html.twig")
     */
    public function listAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'asset', 'accounts']);

        return [
            'report' => $report,
        ];
    }
}

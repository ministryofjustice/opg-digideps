<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\Status;
use App\Service\Client\Internal\ReportApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ClientBenefitsCheckController extends AbstractController
{
    private static array $jmsGroups = [
        'client-benefits-check',
        'client-benefits-check-state',
    ];

    private ReportApi $reportApi;

    public function __construct(ReportApi $reportApi)
    {
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check", name="client_benefits_check")
     * @Template("@App/Report/ClientBenefitsCheck/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (Status::STATE_NOT_STARTED != $report->getStatus()->getClientBenefitsCheckState()['state']) {
            return $this->redirectToRoute('client_benefits_check_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check-summary", name="client_benefits_check_summary")
     * @Template("@App/Report/ClientBenefitsCheck/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        return [
            'report' => $report,
        ];
    }
}

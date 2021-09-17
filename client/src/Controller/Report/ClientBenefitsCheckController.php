<?php

declare(strict_types=1);

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity\Report\ClientBenefitsCheck;
use App\Entity\Report\Status;
use App\Form\Report\ClientBenefitsCheckType;
use App\Service\Client\Internal\ClientBenefitCheckApi;
use App\Service\Client\Internal\ReportApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ClientBenefitsCheckController extends AbstractController
{
    private static array $jmsGroups = [
        'client-benefits-check',
        'client-benefits-check-state',
    ];

    private ReportApi $reportApi;
    private ClientBenefitCheckApi $benefitCheckApi;

    public function __construct(ReportApi $reportApi, ClientBenefitCheckApi $benefitCheckApi)
    {
        $this->reportApi = $reportApi;
        $this->benefitCheckApi = $benefitCheckApi;
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check", name="client_benefits_check")
     * @Template("@App/Report/ClientBenefitsCheck/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function start(int $reportId)
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
     * @Route("/report/{reportId}/client-benefits-check/step/{step}", name="client_benefits_check_step")
     * @Template("@App/Report/ClientBenefitsCheck/step.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function step(Request $request, int $reportId, int $step)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $clientBenefitsCheck = $report->getClientBenefitsCheck() ?: new ClientBenefitsCheck();

        $form = $this->createForm(
            ClientBenefitsCheckType::class,
            $clientBenefitsCheck,
            ['step' => $step]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->benefitCheckApi->post($form->getData());

            return $this->redirectToRoute('client_benefits_check_summary', ['reportId' => $report->getId()]);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'step' => $step,
            'formAction' => is_null($report->getClientBenefitsCheck()) ? 'add' : 'edit',
        ];
    }

    /**
     * @Route("/report/{reportId}/client-benefits-check-summary", name="client_benefits_check_summary")
     * @Template("@App/Report/ClientBenefitsCheck/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summary(int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        return [
            'report' => $report,
        ];
    }
}

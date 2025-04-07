<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OtherInfoController extends AbstractController
{
    private static $jmsGroups = [
        'action-more-info',
        'more-info-state',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private StepRedirector $stepRedirector,
        private ClientApi $clientApi,
    ) {
    }

    /**
     * @Route("/report/{reportId}/any-other-info", name="other_info")
     *
     * @Template("@App/Report/OtherInfo/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getOtherInfoState()['state']) {
            return $this->redirectToRoute('other_info_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/any-other-info/step/{step}", name="other_info_step")
     *
     * @Template("@App/Report/OtherInfo/step.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $totalSteps = 1; // only one step but convenient to reuse the "step" logic and keep things aligned/simple
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('other_info_summary', ['reportId' => $reportId]);
        }
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('other_info', 'other_info_step', 'other_info_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(FormDir\Report\OtherInfoType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->restClient->put('report/'.$reportId, $data, ['more-info']);

            if ('summary' == $fromPage) {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'report' => $report,
            'step' => $step,
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/any-other-info/summary", name="other_info_summary")
     *
     * @Template("@App/Report/OtherInfo/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getOtherInfoState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('other_info', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'otherInfo';
    }
}

<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use App\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LifestyleController extends AbstractController
{
    private static $jmsGroups = [
        'lifestyle',
        'lifestyle-state',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private StepRedirector $stepRedirector,
        private ClientApi $clientApi
    ) {
    }

    /**
     * @Route("/report/{reportId}/lifestyle", name="lifestyle")
     *
     * @Template("@App/Report/Lifestyle/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction(Request $request, $reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid());

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getLifestyleState()['state']) {
            return $this->redirectToRoute('lifestyle_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/lifestyle/step/{step}", name="lifestyle_step")
     *
     * @Template("@App/Report/Lifestyle/step.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function stepAction(Request $request, $reportId, $step)
    {
        $totalSteps = 2;
        if ($step < 1 || $step > $totalSteps) {
            return $this->redirectToRoute('lifestyle_summary', ['reportId' => $reportId]);
        }
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $lifestyle = $report->getLifestyle() ?: new EntityDir\Report\Lifestyle();
        $fromPage = $request->get('from');

        $stepRedirector = $this->stepRedirector
            ->setRoutes('lifestyle', 'lifestyle_step', 'lifestyle_summary')
            ->setFromPage($fromPage)
            ->setCurrentStep($step)
            ->setTotalSteps($totalSteps)
            ->setRouteBaseParams(['reportId' => $reportId]);

        $form = $this->createForm(FormDir\Report\LifestyleType::class, $lifestyle, ['step' => $step]);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Lifestyle */
            $data
                ->setReport($report)
                ->keepOnlyRelevantLifestyleData();

            if (null == $lifestyle->getId()) {
                $this->restClient->post('report/lifestyle', $data, ['lifestyle', 'report-id']);
            } else {
                $this->restClient->put('report/lifestyle/'.$lifestyle->getId(), $data, self::$jmsGroups);
            }

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
            'reportStatus' => $report->getStatus(),
            'form' => $form->createView(),
            'backLink' => $stepRedirector->getBackLink(),
            'skipLink' => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/lifestyle/summary", name="lifestyle_summary")
     *
     * @Template("@App/Report/Lifestyle/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getLifestyleState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('lifestyle', ['reportId' => $reportId]);
        }

        if (!$report->getLifestyle()) { // allow validation with answers all skipped
            $report->setLifestyle(new EntityDir\Report\Lifestyle());
        }

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'lifestyle';
    }
}

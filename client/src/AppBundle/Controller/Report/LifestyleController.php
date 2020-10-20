<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class LifestyleController extends AbstractController
{
    private static $jmsGroups = [
        'lifestyle',
        'lifestyle-state',
    ];

    /**
     * @Route("/report/{reportId}/lifestyle", name="lifestyle")
     * @Template("AppBundle:Report/Lifestyle:start.html.twig")
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getLifestyleState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('lifestyle_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/lifestyle/step/{step}", name="lifestyle_step")
     * @Template("AppBundle:Report/Lifestyle:step.html.twig")
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


        $stepRedirector = $this->stepRedirector()
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

            if ($lifestyle->getId() == null) {
                $this->restClient->post('report/lifestyle', $data, ['lifestyle', 'report-id']);
            } else {
                $this->restClient->put('report/lifestyle/' . $lifestyle->getId(), $data, self::$jmsGroups);
            }

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirect($stepRedirector->getRedirectLinkAfterSaving());
        }

        return [
            'report'       => $report,
            'step'         => $step,
            'reportStatus' => $report->getStatus(),
            'form'         => $form->createView(),
            'backLink'     => $stepRedirector->getBackLink(),
            'skipLink'     => $stepRedirector->getSkipLink(),
        ];
    }

    /**
     * @Route("/report/{reportId}/lifestyle/summary", name="lifestyle_summary")
     * @Template("AppBundle:Report/Lifestyle:summary.html.twig")
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getLifestyleState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('lifestyle', ['reportId' => $reportId]);
        }

        if (!$report->getLifestyle()) { //allow validation with answers all skipped
            $report->setLifestyle(new EntityDir\Report\Lifestyle());
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report'             => $report,
            'status'             => $report->getStatus()
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

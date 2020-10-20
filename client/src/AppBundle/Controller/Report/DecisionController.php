<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;

use AppBundle\Service\Client\Internal\ReportApi;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class DecisionController extends AbstractController
{
    private static $jmsGroups = [
        'decision',
        'mental-capacity',
        'decision-status',
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    )
    {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("/report/{reportId}/decisions", name="decisions")
     * @Template("AppBundle:Report/Decision:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getDecisionsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/mental-capacity", name="decisions_mental_capacity")
     * @Template("AppBundle:Report/Decision:mentalCapacity.html.twig")
     *
     * @param Request $request
     * @param int $reportId
     *
     * @return array|RedirectResponse
     */
    public function mentalCapacityAction(Request $request, int $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = $request->get('from') == 'summary';

        $mc = $report->getMentalCapacity();
        if ($mc == null) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $form = $this->createForm(FormDir\Report\MentalCapacityType::class, $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/' . $reportId . '/mental-capacity', $data, ['mental-capacity']);
            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute($fromSummaryPage ? 'decisions_summary' : 'decisions_mental_assessment', ['reportId' => $reportId]);
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'decisions_summary' : 'decisions', ['reportId'=>$report->getId()]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('decisions_mental_assessment', ['reportId'=>$report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/mental-assessment", name="decisions_mental_assessment")
     * @Template("AppBundle:Report/Decision:mentalAssessment.html.twig")
     */
    public function mentalAssessmentAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = $request->get('from') == 'summary';

        $mc = $report->getMentalCapacity();
        if ($mc == null) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $form = $this->createForm(FormDir\Report\MentalAssessment::class, $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $data->setReport($report);

            $this->restClient->put('report/' . $reportId . '/mental-capacity', $data, ['mental-assessment-date']);
            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute($fromSummaryPage ? 'decisions_summary' : 'decisions_exist', ['reportId' => $reportId]);
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'decisions_summary' : 'decisions_mental_capacity', ['reportId'=>$report->getId()]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('decisions_exist', ['reportId'=>$report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/exist", name="decisions_exist")
     * @Template("AppBundle:Report/Decision:exist.html.twig")
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\DecisionExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['hasDecisions']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decisions_add', ['reportId' => $reportId, 'from'=>'decisions_exist']);
                case 'no':
                    $this->restClient->put('report/' . $reportId, $report, ['reasonForNoDecisions']);
                    foreach ($report->getDecisions() as $decision) {
                        $this->restClient->delete('/report/decision/' . $decision->getId());
                    }
                    return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('decisions_mental_assessment', ['reportId'=>$reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('decisions_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/add", name="decisions_add")
     * @Template("AppBundle:Report/Decision:add.html.twig")
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = new EntityDir\Report\Decision();
        $from = $request->get('from');

        $form = $this->createForm(FormDir\Report\DecisionType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->post('report/decision', $data, ['decision', 'report-id']);

            return $this->redirect($this->generateUrl('decisions_add_another', ['reportId' => $reportId]));
        }

        //TODO use $backLinkRoute logic and align to other controllers
        $backLink = $this->routeExists($from) ? $this->generateUrl($from, ['reportId'=>$reportId]) : '';

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/add_another", name="decisions_add_another")
     * @Template("AppBundle:Report/Decision:addAnother.html.twig")
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-decisions']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decisions_add', ['reportId' => $reportId, 'from'=>'decisions_add_another']);
                case 'no':
                    return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/edit/{decisionId}", name="decisions_edit")
     * @Template("AppBundle:Report/Decision:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $decisionId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->restClient->get('report/decision/' . $decisionId, 'Report\\Decision');
        $decision->setReport($report);

        $form = $this->createForm(FormDir\Report\DecisionType::class, $decision);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/decision', $data, ['decision']);

            $request->getSession()->getFlashBag()->add('notice', 'Decision edited');

            return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('decisions_summary', ['reportId'=>$reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/summary", name="decisions_summary")
     * @Template("AppBundle:Report/Decision:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getDecisionsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED && $fromPage != 'skip-step') {
            return $this->redirectToRoute('decisions', ['reportId' => $reportId]);
        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report' => $report,
            'status' => $report->getStatus()
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/{decisionId}/delete", name="decisions_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $decisionId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete("/report/decision/{$decisionId}");

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Decision deleted'
            );

            return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
        }

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->restClient->get('report/decision/' . $decisionId, 'Report\\Decision');

        return [
            'translationDomain' => 'report-decisions',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.description', 'value' => $decision->getDescription()],
            ],
            'backLink' => $this->generateUrl('decisions', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'decisions';
    }
}

<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Entity\User;
use App\Form as FormDir;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DecisionController extends AbstractController
{
    private static $jmsGroups = [
        'decision',
        'mental-capacity',
        'decision-status',
        'significantDecisionMade',
    ];

    public function __construct(
        private RestClient $restClient,
        private ReportApi $reportApi,
        private ClientApi $clientApi
    ) {
    }

    /**
     * @Route("/report/{reportId}/decisions", name="decisions")
     *
     * @Template("@App/Report/Decision/start.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        /** @var User $user */
        $user = $this->getUser();
        $isMultiClientDeputy = 'ROLE_LAY_DEPUTY' == $user->getRoleName() ? $this->clientApi->checkDeputyHasMultiClients($user->getDeputyUid()) : null;

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED != $report->getStatus()->getDecisionsState()['state']) {
            return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
            'isMultiClientDeputy' => $isMultiClientDeputy,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/mental-capacity", name="decisions_mental_capacity")
     *
     * @Template("@App/Report/Decision/mentalCapacity.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function mentalCapacityAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = 'summary' == $request->get('from');

        $mc = $report->getMentalCapacity();
        if (null == $mc) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $form = $this->createForm(FormDir\Report\MentalCapacityType::class, $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/'.$reportId.'/mental-capacity', $data, ['mental-capacity']);
            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute($fromSummaryPage ? 'decisions_summary' : 'decisions_mental_assessment', ['reportId' => $reportId]);
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'decisions_summary' : 'decisions', ['reportId' => $report->getId()]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('decisions_mental_assessment', ['reportId' => $report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/mental-assessment", name="decisions_mental_assessment")
     *
     * @Template("@App/Report/Decision/mentalAssessment.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function mentalAssessmentAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = 'summary' == $request->get('from');

        $mc = $report->getMentalCapacity();
        if (null == $mc) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $form = $this->createForm(FormDir\Report\MentalAssessment::class, $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->put('report/'.$reportId.'/mental-capacity', $data, ['mental-assessment-date']);
            if ($fromSummaryPage) {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            return $this->redirectToRoute($fromSummaryPage ? 'decisions_summary' : 'decisions_exist', ['reportId' => $reportId]);
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'decisions_summary' : 'decisions_mental_capacity', ['reportId' => $report->getId()]),
            'skipLink' => $fromSummaryPage ? null : $this->generateUrl('decisions_exist', ['reportId' => $report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/exist", name="decisions_exist")
     *
     * @Template("@App/Report/Decision/exist.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\Report\DecisionExistType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $report = $form->getData();
            $answer = $form['significantDecisionsMade']->getData();

            if ('Yes' == $answer) {
                $report->setReasonForNoDecisions(null);

                $this->updateReport($report, $reportId, ['significantDecisionsMade', 'reasonForNoDecisions']);

                return $this->redirectToRoute('decisions_add', ['reportId' => $reportId, 'from' => 'decisions_exist']);
            } else {
                foreach ($report->getDecisions() as $decision) {
                    $this->restClient->delete('/report/decision/'.$decision->getId());
                }

                // this must proceed the deletion above if deputy switches from 'yes' to 'no' as it will not persist the 'reason for decision' answer
                $this->updateReport($report, $reportId, ['significantDecisionsMade', 'reasonForNoDecisions']);

                return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('decisions_mental_assessment', ['reportId' => $reportId]);
        if ('summary' == $request->get('from')) {
            $backLink = $this->generateUrl('decisions_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    private function updateReport($report, $reportId, $fields)
    {
        $this->restClient->put('report/'.$reportId, $report, $fields);
    }

    /**
     * @Route("/report/{reportId}/decisions/add", name="decisions_add")
     *
     * @Template("@App/Report/Decision/add.html.twig")
     *
     * @return array|RedirectResponse
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

        // TODO use $backLinkRoute logic and align to other controllers
        try {
            $backLink = $this->generateUrl($from, ['reportId' => $reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report,
            ];
        } catch (RouteNotFoundException $e) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report,
            ];
        }
    }

    /**
     * @Route("/report/{reportId}/decisions/add_another", name="decisions_add_another")
     *
     * @Template("@App/Report/Decision/addAnother.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(FormDir\AddAnotherRecordType::class, $report, ['translation_domain' => 'report-decisions']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decisions_add', ['reportId' => $reportId, 'from' => 'decisions_add_another']);
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
     *
     * @Template("@App/Report/Decision/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, $reportId, $decisionId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->restClient->get('report/decision/'.$decisionId, 'Report\\Decision');
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
            'backLink' => $this->generateUrl('decisions_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/summary", name="decisions_summary")
     *
     * @Template("@App/Report/Decision/summary.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (EntityDir\Report\Status::STATE_NOT_STARTED == $report->getStatus()->getDecisionsState()['state'] && 'skip-step' != $fromPage) {
            return $this->redirectToRoute('decisions', ['reportId' => $reportId]);
        }

        $numberOfDecisions = count($report->getDecisions());

        return [
            'comingFromLastStep' => 'skip-step' == $fromPage || 'last-step' == $fromPage,
            'report' => $report,
            'status' => $report->getStatus(),
            'numOfDecisions' => $numberOfDecisions,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/{decisionId}/delete", name="decisions_delete")
     *
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @return array|RedirectResponse
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
        $decision = $this->restClient->get('report/decision/'.$decisionId, 'Report\\Decision');

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

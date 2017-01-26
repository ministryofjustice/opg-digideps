<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DecisionController extends AbstractController
{
    private static $jmsGroups = [
        'decision',
        'mental-capacity'
    ];


    /**
     * @Route("/report/{reportId}/decisions", name="decisions")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $decisionValid = count($report->getDecisions()) > 0 || !empty($report->getReasonForNoDecisions());
        if ($decisionValid || $report->getMentalCapacity()) {
            return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/mental-capacity", name="decisions_mental_capacity")
     * @Template()
     */
    public function mentalCapacityAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $mc = $report->getMentalCapacity();
        if ($mc == null) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $form = $this->createForm(new FormDir\Report\MentalCapacityType(), $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->put('report/'.$reportId.'/mental-capacity', $data, ['mental-capacity']);
            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }

            $route = ($fromPage == 'summary') ? 'decisions_summary' : 'decisions_mental_assessment';

            return $this->redirect($this->generateUrl($route, ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('decisions', ['reportId'=>$report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * //TODO consider to merge this as a step of mentalCapacity action above
     * @Route("/report/{reportId}/decisions/mental-assessment", name="decisions_mental_assessment")
     * @Template()
     */
    public function mentalAssessmentAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');
        $routeForward = ($fromPage == 'summary') ? 'decisions_summary' : 'decisions_exist';
        $routeBack = ($fromPage == 'summary') ? 'decisions_summary' : 'decisions_mental_capacity';

        $mc = $report->getMentalCapacity();
        if ($mc == null) {
            $mc = new EntityDir\Report\MentalCapacity();
        }

        $form = $this->createForm(new FormDir\Report\MentalAssessment(), $mc);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $data->setReport($report);

            $this->getRestClient()->put('report/'.$reportId.'/mental-capacity', $data, ['mental-assessment-date']);
            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add('notice', 'Answer edited');
            }


            return $this->redirect($this->generateUrl($routeForward, ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($routeBack, ['reportId'=>$report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/exist", name="decisions_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\DecisionExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['hasDecisions']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decisions_add', ['reportId' => $reportId, 'from'=>'decisions_exist']);
                case 'no':
                    $this->get('rest_client')->put('report/' . $reportId, $report, ['reasonForNoDecisions']);
                    foreach ($report->getDecisions() as $decision) {
                        $this->getRestClient()->delete("/report/decision/".$decision->getId());
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
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = new EntityDir\Report\Decision();
        $from = $request->get('from');

        $form = $this->createForm(new FormDir\Report\DecisionType(), $decision);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/decision', $data, ['decision', 'report-id']);

            return $this->redirect($this->generateUrl('decisions_add_another', ['reportId' => $reportId]));
        }

        $backLink = $this->routeExists($from) ? $this->generateUrl($from, ['reportId'=>$reportId]) : '';

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/decisions/add_another", name="decisions_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\Report\DecisionAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
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
     * @Template()
     */
    public function editAction(Request $request, $reportId, $decisionId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->getRestClient()->get('report/decision/'.$decisionId, 'Report\\Decision');
        $decision->setReport($report);

        $form = $this->createForm(new FormDir\Report\DecisionType(), $decision);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->put('report/decision', $data, ['decision']);

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
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (!$report->getMentalCapacity()) {
            return $this->redirectToRoute('decisions', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/decisions/{decisionId}/delete", name="decisions_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $decisionId)
    {
        $this->getRestClient()->delete("/report/decision/{$decisionId}");

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Decision deleted'
        );

        return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
    }
}

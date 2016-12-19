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
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);

        $decisionValid = count($report->getDecisions()) > 0 || !empty($report->getReasonForNoDecisions());
        if ($decisionValid && $report->getMentalCapacity()) {
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
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);
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

            $route = ($fromPage == 'summary') ? 'decisions_summary' : 'decisions_exist';

            return $this->redirect($this->generateUrl($route, ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('decisions', ['reportId'=>$report->getId()]),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/exist", name="decisions_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\DecisionExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['hasDecisions']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decision_add', ['reportId' => $reportId]);
                case 'no':
                    $this->get('restClient')->put('report/' . $reportId, $report, ['reasonForNoDecisions']);
                    foreach($report->getDecisions() as $decision) {
                        $this->getRestClient()->delete("/report/decision/".$decision->getId());
                    }
                    return $this->redirectToRoute('decisions_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('decisions_mental_capacity', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'summary') {
            $backLink = $this->generateUrl('decisions_summary', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/add", name="decision_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);
        $decision = new EntityDir\Report\Decision();

        $form = $this->createForm(new FormDir\Report\DecisionType(), $decision);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/decision', $data, ['decision', 'report-id']);

            return $this->redirect($this->generateUrl('decision_add_another', ['reportId' => $reportId]));
        }

        $backLink = $this->generateUrl('decisions_exist', ['reportId'=>$reportId]);
        if ( $request->get('from') == 'another') {
            $backLink = $this->generateUrl('decision_add_another', ['reportId'=>$reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/decisions/add_another", name="decision_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\Report\DecisionAddAnotherType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('decision_add', ['reportId' => $reportId, 'from'=>'another']);
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
     * @Route("/report/{reportId}/decisions/edit/{decisionId}", name="decision_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId, $decisionId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);
        $decision = $this->getRestClient()->get('report/decision/'.$decisionId, 'Report\\Decision');
        $decision->setReport($report);

        $form = $this->createForm(new FormDir\Report\DecisionType(), $decision);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $request->getSession()->getFlashBag()->add('notice', 'Record edited');

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
        $report = $this->getReportIfReportNotSubmitted($reportId, self::$jmsGroups);

        return [
            'report' => $report,
        ];
    }


    /**
     * @Route("/report/{reportId}/decisions/{decisionId}/delete", name="decision_delete")
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

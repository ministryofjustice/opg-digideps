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
    /**
     * @Route("/report/{reportId}/decisions", name="decisions")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function listAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['decision']);

        $decisions = $report->getDecisions();

        if (empty($decisions) && $report->isDue() == false) {
            return $this->redirect($this->generateUrl('add_decision', ['reportId' => $reportId]));
        }

        return [
            'decisions' => $decisions,
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/add", name="add_decision")
     * @Template("AppBundle:Report/Decision:add.html.twig")
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $decision = new EntityDir\Report\Decision();
        $decision->setReport($report);
        $form = $this->createForm(new FormDir\Report\DecisionType(), $decision);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/decision', $data, ['decision', 'report-id']);

            return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/{id}/edit", name="edit_decision")
     * @Template("AppBundle:Report/Decision:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $decision = $this->getRestClient()->get('report/decision/'.$id, 'Report\\Decision');

        $form = $this->createForm(new FormDir\Report\DecisionType(), $decision);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->put('report/decision', $data, ['decision']);

            return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/decisions/{id}/delete", name="delete_decision")
     *
     * @param int $id
     * 
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        $this->getRestClient()->delete("/report/decision/{$id}");

        return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/decisions/delete-nonereason", name="delete_nonereason_decisions")
     */
    public function deleteReasonAction($reportId)
    {
        //just do some checks to make sure user is allowed to update this report
        $report = $this->getReport($reportId);

        if (!empty($report)) {
            $report->setReasonForNoDecisions(null);
            $this->getRestClient()->put('report/'.$report->getId(), $report, ['reasonForNoDecisions']);
        }

        return $this->redirect($this->generateUrl('decisions', ['reportId' => $report->getId()]));
    }

    /**
     * @Route("/report/{reportId}/decisions/nonereason", name="edit_decisions_nonereason")
     * @Template("AppBundle:Report/Decision:edit_none_reason.html.twig")
     */
    public function noneReasonAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $form = $this->createForm(new FormDir\Report\ReasonForNoDecisionType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('report/'.$reportId, $data, ['reasonForNoDecisions']);

            return $this->redirect($this->generateUrl('decisions', ['reportId' => $reportId]));
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * Sub controller action called when the no decision form is embedded in another page.
     *
     * @Template("AppBundle:Report/Decision:_none_reason_form.html.twig")
     */
    public function _noneReasonFormAction(Request $request, $reportId)
    {
        $actionUrl = $this->generateUrl('edit_decisions_nonereason', ['reportId' => $reportId]);
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $form = $this->createForm(new FormDir\Report\ReasonForNoDecisionType(), $report, ['action' => $actionUrl]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('report/'.$reportId, $data, ['reasonForNoDecisions']);
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }
}

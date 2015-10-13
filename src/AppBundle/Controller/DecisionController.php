<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Service\ReportStatusService;

class DecisionController extends Controller
{
    /**
     * @Route("/report/{reportId}/decisions/delete-reason", name="delete_reason_decisions")
     */
    public function deleteReasonAction($reportId)
    {
        $util = $this->get('util');

        //just do some checks to make sure user is allowed to update this report
        $report = $util->getReport($reportId, ['transactions']);

        if(!empty($report)){
            $report->setReasonForNoDecisions(null);
            $this->get('restClient')->put('report/'.$report->getId(),$report);
        }
        return $this->redirect($this->generateUrl('decisions', ['reportId' => $report->getId()]));
    }

    /**
     * @Route("/report/{reportId}/decisions/delete/{id}", name="delete_decision")
     * @param integer $id
     */
    public function deleteAction($reportId,$id)
    {
        $util = $this->get('util');

        //just do some checks to make sure user is allowed to delete this contact
        $report = $util->getReport($reportId, ['transactions']);

        if(!empty($report) && in_array($id, $report->getDecisions())){
            $this->get('restClient')->delete("/report/decision/{$id}");
        }
        return $this->redirect($this->generateUrl('decisions', [ 'reportId' => $reportId ]));
    }

    /**
     * action [list, add, edit, delete-confirm, edit-reason, delete-reason-confirm ]
     * @Route("/report/{reportId}/decisions/{action}/{id}", name="decisions", defaults={ "action" = "list", "id" = " "})
     * @Template()
     */
    public function decisionsAction($reportId,$action,$id)
    {
        $request = $this->getRequest();
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $util = $this->get('util');

        // just needed for title etc,
        $report = $util->getReport($reportId);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        if(in_array($action, [ 'edit', 'delete-confirm'])){
            if (!in_array($id, $report->getDecisions())) {
               throw new \RuntimeException("Decision not found.");
            }
            $decision = $restClient->get('/report/decision/' . $id, 'Decision');

            $form = $this->createForm(new FormDir\DecisionType([
                'clientInvolvedBooleanEmptyValue' => $this->get('translator')->trans('clientInvolvedBoolean.defaultOption', [], 'report-decisions')
            ]), $decision, [ 'action' => $this->generateUrl('decisions',[ 'reportId' => $reportId, 'action' => 'edit', 'id' => $id ])]);

        }else{
            $decision = new EntityDir\Decision;

            $form = $this->createForm(new FormDir\DecisionType([
                'clientInvolvedBooleanEmptyValue' => $this->get('translator')->trans('clientInvolvedBoolean.defaultOption', [], 'report-decisions')
            ]), $decision, [ 'action' => $this->generateUrl('decisions',[ 'reportId' => $reportId, 'action' => 'add' ])]);
        }

        $decision->setReportId($reportId);
        $decision->setReport($report);

        $noDecision = $this->createForm(new FormDir\ReasonForNoDecisionType(), null, [ 'action' => $this->generateUrl('decisions', [ 'reportId' => $reportId])."#pageBody" ]);
        
        $reason = $report->getReasonForNoDecisions();
        $mode = empty($reason)? 'add':'edit';
        $noDecision->setData([ 'reason' => $reason, 'mode' => $mode ]);
        
        if ($request->isMethod('POST')) {

            $form->handleRequest($request);
            $noDecision->handleRequest($request);

            if($form->get('save')->isClicked()){

                if ($form->isValid()) {

                    $this->handleAddEditDecision($action,$form,$report);

                    return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]));
                }
            } elseif ($noDecision->get('saveReason')->isClicked()){
                
                if($noDecision->isValid()){
                    $this->handleReasonForNoDecision($action, $noDecision, $reportId);
                    return $this->redirect($this->generateUrl('decisions',[ 'reportId' => $report->getId()]));
                }
            }
        }
        $reportStatusService = new ReportStatusService($report, $this->get('translator'));
        

        return [
            'decisions' => $restClient->get('report/' . $reportId . '/decisions', 'Decision[]'),
            'form' => $form->createView(),
            'no_decision' => $noDecision->createView(),
            'report' => $report,
            'reportStatus' => $reportStatusService,
            'client' => $util->getClient($report->getClient()),
            'action' => $action,
        ];
    }

    /**
     *
     * @param string $action
     */
    protected function handleAddEditDecision($action,$form,$report)
    {
        $restClient = $this->get('restClient');

         if($action == 'add'){
            // add decision
            $restClient->post('report/decision', $form->getData());

            //lets clear any reason for no decisions they might have added previously
            $report->setReasonForNoDecisions(null);
            $this->get('restClient')->put('report/'.$report->getId(),$report);
        }else{
            // edit decision
            $restClient->put('report/decision', $form->getData());
        }
    }

    /**
     *
     * @param string $action
     * @param type $noDecision
     * @param integer $reportId
     */
    protected function handleReasonForNoDecision($action,$noDecision,$reportId)
    {
        $restClient = $this->get('restClient');
        $util = $this->get('util');

        $formData = $noDecision->getData();

        $report = $util->getReport($reportId);
        $report->setReasonForNoDecisions($formData['reason']);
        $restClient->put('report/'.$report->getId(),$report);
    }
}

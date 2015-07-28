<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DecisionController extends Controller
{
    /**
     * @Route("/report/{reportId}/decisions/delete-reason", name="delete_reason_decisions")
     */
    public function deleteReasonAction($reportId)
    {
        $util = $this->get('util');

        //just do some checks to make sure user is allowed to update this report
        $report = $util->getReport($reportId, $this->getUser()->getId(), ['transactions']);

        if(!empty($report)){
            $report->setReasonForNoDecisions(null);
            $this->get('apiclient')->putC('report/'.$report->getId(),$report);
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
        $report = $util->getReport($reportId, $this->getUser()->getId(), ['transactions']);

        if(!empty($report) && in_array($id, $report->getDecisions())){
            $this->get('apiclient')->delete('delete_decision', [ 'parameters' => [ 'id' => $id ]]);
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
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $util = $this->get('util');

        // just needed for title etc,
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        if(in_array($action, [ 'edit', 'delete-confirm']) && in_array($id,$report->getDecisions())){
            $decision = $apiClient->getEntity('Decision','get_report_decision', [ 'parameters' => ['id' => $id ] ]);

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

        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }

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
            }else{
                if($reportSubmit->isValid()){
                    if($report->readyToSubmit()){
                        return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                    }
                }

            }
        }

        return [
            'decisions' => $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'parameters' => [ 'reportId' => $reportId ]]),
            'form' => $form->createView(),
            'no_decision' => $noDecision->createView(),
            'report' => $report,
            'client' => $util->getClient($report->getClient()),
            'action' => $action,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }

    /**
     *
     * @param string $action
     */
    protected function handleAddEditDecision($action,$form,$report)
    {
        $apiClient = $this->get('apiclient');

         if($action == 'add'){
            // add decision
            $apiClient->postC('add_decision', $form->getData());

            //lets clear any reason for no decisions they might have added previously
            $report->setReasonForNoDecisions(null);
            $this->get('apiclient')->putC('report/'.$report->getId(),$report);
        }else{
            // edit decision
            $apiClient->putC('update_decision', $form->getData());
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
        $apiClient = $this->get('apiclient');
        $util = $this->get('util');

        $formData = $noDecision->getData();

        $report = $util->getReport($reportId, $this->getUser()->getId());
        $report->setReasonForNoDecisions($formData['reason']);
        $apiClient->putC('report/'.$report->getId(),$report);
    }
}

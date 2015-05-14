<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;

class DecisionController extends Controller
{
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
     * action [list, add, edit, delete-confirm ]
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
        if ($redirectResponse = $this->get('reportSubmitter')->isReportSubmitted($report)) {
            return $redirectResponse;
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
           
            if($form->get('save')->isClicked()){
                if ($form->isValid()) {
                    
                    if($action == 'add'){
                        // add decision
                        $apiClient->postC('add_decision', $form->getData());
                    }else{
                        // edit decision
                        $apiClient->putC('update_decision', $form->getData());
                    }
                    return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]));
                }
            }
        }
        
        return [
            'decisions' => $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'parameters' => [ 'reportId' => $reportId ]]),
            'form' => $form->createView(),
            'report' => $report,
            'client' => $util->getClient($report->getClient()),
            'action' => $action,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }
}
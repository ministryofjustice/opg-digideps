<?php
namespace AppBundle\Controller;

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
     * @Template("AppBundle:Decision:list.html.twig")
     * @param integer $reportId
     * @return array
     */
    public function listAction($reportId) {
        
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $decisions = $restClient->get('report/' . $reportId . '/decisions', 'Decision[]');
        $client = $this->getClient($report->getClient());

        return [
            'decisions' => $decisions,
            'report' => $report,
            'client' => $client
        ];
        
    }

    
    /**
     * @Route("/report/{reportId}/decisions/add", name="add_decision")
     * @Template("AppBundle:Decision:add.html.twig")
     */
    public function addAction(Request $request, $reportId) {
        
        $report = $this->getReportIfReportNotSubmitted($reportId);
        
        $decision = new EntityDir\Decision;
        $form = $this->createForm(new FormDir\DecisionType(), $decision);
        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $data->setReportId($reportId);

            $this->get('restClient')->post('report/decision', $data);

            //lets clear any reason for no decisions they might have added previously
            $report->setReasonForNoDecisions(null);
            $this->get('restClient')->put('report/'. $report->getId(),$report);
            
            return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]) );
        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    
    /**
     * @Route("/report/{reportId}/decision/{id}", name="edit_decision")
     * @Template("AppBundle:Decision:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $id) {

        $restClient = $this->get('restClient');
        
        $report = $this->getReportIfReportNotSubmitted($reportId);

        if (!in_array($id, $report->getDecisions())) {
            throw new \RuntimeException("Decision not found.");
        }
        $decision = $restClient->get('report/decision/' . $id, 'Decision');
        
        $form = $this->createForm(new FormDir\DecisionType(), $decision);
        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $data->setReportId($reportId);

            $this->get('restClient')->post('report/decision', $data);
            
            return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]));
        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    
    /**
     * @Route("/report/{reportId}/decision/delete/{id}", name="delete_decision")
     * @param integer $id
     * 
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        //just do some checks to make sure user is allowed to delete this contact
        $report = $this->getReport($reportId, ['basic']);

        if(!empty($report) && in_array($id, $report->getDecisions())){
            $this->get('restClient')->delete("/report/decision/{$id}");
        }
        
        return $this->redirect($this->generateUrl('decisions', [ 'reportId' => $reportId ]));
    
    }
    

    /**
     * @Route("/report/{reportId}/decisions/delete-nonereason", name="delete_nonereason_decisions")
     */
    public function deleteReasonAction($reportId)
    {
        //just do some checks to make sure user is allowed to update this report
        $report = $this->getReport($reportId, ['basic', 'transactions']);

        if(!empty($report)){
            $report->setReasonForNoDecisions(null);
            $this->get('restClient')->put('report/'.$report->getId(),$report);
        }
        return $this->redirect($this->generateUrl('decisions', ['reportId' => $report->getId()]));
    }


    /**
     * @Route("/report/{reportId}/decisions/nonereason", name="edit_decisions_nonereason")
     * @Template("AppBundle:Decision:edit_none_reason.html.twig")
     */  
    public function noneReasonAction(Request $request, $reportId) {
        
        $report = $this->getReportIfReportNotSubmitted($reportId);

        $form = $this->createForm(new FormDir\ReasonForNoDecisionType(), $report);
        $form->handleRequest($request);

        if($form->isValid()){
        
            $data = $form->getData();
            $this->get('restClient')->put('report/'. $reportId,$data);

            return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]));
            
        }

        $client = $this->getClient($report->getClient());
        
        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];
        
    }
    

    /**
     * Sub controller action called when the no decision form is embedded in another page.
     *
     * @Template("AppBundle:Decision:_none_reason_form.html.twig")
     */
    public function _noneReasonFormAction(Request $request, $reportId)
    {

        $actionUrl = $this->generateUrl('edit_decisions_nonereason', ['reportId'=>$reportId]);
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $form = $this->createForm(new FormDir\ReasonForNoDecisionType(), $report, ['action' => $actionUrl]);
        $form->handleRequest($request);

        if($form->isValid()){
            $data = $form->getData();
            $this->get('restClient')->put('report/'. $reportId,$data);
        }
        
        return [
            'form' => $form->createView(),
            'report' => $report
        ];
    }
    
    
    /**
     *
     * @param integer $reportId
     * @return EntityDir\Report
     *
     * @throws \RuntimeException if report is submitted
     */
    private function getReportIfReportNotSubmitted($reportId, $addClient = true)
    {
        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        if ($addClient) {
            $client = $this->getClient($report->getClient());
            $report->setClientObject($client);
        }

        return $report;
    }

}

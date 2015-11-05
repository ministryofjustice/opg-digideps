<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


class DecisionController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/decisions", name="decisions")
     * @Template("AppBundle:Decision:list.html.twig")
     */
    public function listAction(Request $request, $reportId) {

        
        
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
            
            return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]) . "#pageBody");
        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    
    /**
     * @Route("/report/{reportId}/decisions/{id}", name="edit_decisions")
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
            
            return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]) . "#pageBody");
        }

        $client = $this->getClient($report->getClient());

        return [
            'form' => $form->createView(),
            'report' => $report,
            'client' => $client
        ];

    }

    
    /**
     * @Route("/report/{reportId}/decisions/delete/{id}", name="delete_decisions")
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
     * @Route("/report/{reportId}/decisions/delete-reason", name="delete_reason_decisions")
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
     * Return the small template with the form for no decisions
     *
     * @Template("AppBundle:Decision:_noDecisions.html.twig")
     */
    public function _noDecisionsAction(Request $request, $reportId)
    {

        $report = $this->getReportIfReportNotSubmitted($reportId);
        
        $form = $this->createForm(new FormDir\ReasonForNoDecisionType(), $report);
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

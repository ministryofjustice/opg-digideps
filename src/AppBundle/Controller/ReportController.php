<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ReportType;
use AppBundle\Entity\Client;
use AppBundle\Entity\Report;
use AppBundle\Entity\Decision;
use AppBundle\Service\ApiClient;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/create/{clientId}", name="report_create")
     * @Template()
     */
    public function createAction($clientId)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient');
       
        $client = $this->getClient($clientId);
        
        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();
        
        //lets check if this  user already has another report, if not start date should be court order date
        $report = new EntityDir\Report();
        $report->setClient($client->getId());
        
        $reports = $client->getReports();
        
        if(empty($reports)){
            $report->setStartDate($client->getCourtDate());
        }
        
        //if client has property & affairs and health & welfare then give them property & affairs
        //else give them health and welfare
        if(count($allowedCourtOrderTypes) > 1){
            $report->setCourtOrderType(Report::PROPERTY_AND_AFFAIRS);
        }else{
            $report->setCourtOrderType($allowedCourtOrderTypes[0]);
        }
        
        $form = $this->createForm(new FormDir\ReportType(), $report,
                                  [ 'action' => $this->generateUrl('report_create', [ 'clientId' => $clientId ])]);
        $form->handleRequest($request);
       
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $response = $apiClient->postC('add_report', $form->getData());
                return $this->redirect($this->generateUrl('report_overview', [ 'reportId' => $response['report'] ]));
            }
        }
        
        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/{reportId}/overview", name="report_overview")
     * @Template()
     */
    public function overviewAction($reportId)
    {
        $report = $this->getReport($reportId);
        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @Route("/{reportId}/add-contact", name="add_contact")
     * @Template()
     */
    public function addContactAction($reportId)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient');
        
        $contact = new EntityDir\Contact();
        
        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $contact = $form->getData();
                $contact->setReport($reportId);
                
                $apiClient->postC('add_report_contact', $contact);
                return $this->redirect($this->generateUrl('list_contacts', [ 'reportId' => $reportId ]));
            }
        }
        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/list-contacts/{reportId}", name="list_contacts")
     * @Template()
     */
    public function listContactAction($reportId)
    {
        $report = $this->getReport($reportId);
        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @Route("/{reportId}/decisions/add", name="add_decision")
     * @Template("AppBundle:Report:listDecision.html.twig")
     */
    public function addDecisionAction($reportId)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        // just needed for title etc,
        $report = $this->getReport($reportId);
        $decision = new Decision;
        $decision->setReportId($reportId);
        $decision->setReport($report);
        
        $form = $this->createForm(new FormDir\DecisionType([
            'clientInvolvedBooleanEmptyValue' => $this->get('translator')->trans('clientInvolvedBoolean.defaultOption', [], 'report-decisions')
        ]), $decision);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add decision
                $response = $apiClient->postC('add_decision', $form->getData());
                
                return $this->redirect($this->generateUrl('list_decisions', ['reportId'=>$reportId]));
            }
        }

        return [
            'decisions' => $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'query' => [ 'reportId' => $reportId ]]),
            'form' => $form->createView(),
            'report' => $report,
            'client' => $this->getClient($report->getClient()), //to pass,
            'showAddForm' => true
        ];
    }
    
    
    /**
     * @Route("/{reportId}/decisions", name="list_decisions")
     * @Template()
     */
    public function listDecisionAction($reportId)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $report = $this->getReport($reportId);
        $decisions = $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'query' => [ 'reportId' => $reportId ]]);
        
        if (count($decisions) === 0) {
            return $this->forward('AppBundle:Report:addDecision', ['reportId'=> $reportId]);
        }
        
        return [
            'decisions' => $decisions,
            'report' => $report,
            'showAddForm' => false,
            'client' => $this->getClient($report->getClient()), //to pass,
        ];
    }
    
    /**
     * @Route("/{reportId}/accounts", name="list_accounts")
     * @Template()
     */
    public function listAccountsAction($reportId)
    {
        $report = $this->getReport($reportId);
        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @Route("/{reportId}/assets", name="list_assets")
     * @Template()
     */
    public function listAssetsAction($reportId)
    {
        $report = $this->getReport($reportId);
        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @param integer $clientId
     * 
     * @return Client
     */
    private function getClient($clientId)
    {
        return $this->get('apiclient')->getEntity('Client','find_client_by_id', [ 'query' => [ 'id' => $clientId ]]);
    }
    
    /**
     * @param integer $reportId
     * 
     * @return Report
     */
    private function getReport($reportId)
    {
        return $this->get('apiclient')->getEntity('Report', 'find_report_by_id', [ 'query' => [ 'id' => $reportId ]]);
    }
    
}
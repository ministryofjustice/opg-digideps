<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ReportType;
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
       
        $client = $apiClient->getEntity('Client','find_client_by_id', [ 'query' => [ 'id' => $clientId ]]);
        
        $allowedCourtOrderTypes = $client->getAllowedCourtOrderTypes();
        
        //lets check if this  user already has another report, if not start date should be court order date
        $report = new Report();
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
        
        $form = $this->createForm(new ReportType(), $report,
                                  [ 'action' => $this->generateUrl('report_create', [ 'clientId' => $clientId ])]);
        $form->handleRequest($request);
       
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $response = $apiClient->postC('add_report', $form->getData());
                return $this->redirect($this->generateUrl('report_overview', [ 'id' => $response['report'] ]));
            }
        }
        
        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/overview/{id}", name="report_overview")
     * @Template()
     */
    public function overviewAction($id)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        $report = $apiClient->getEntity('Report', 'find_report_by_id', [ 'query' => [ 'id' => $id ]]);
        $client = $apiClient->getEntity('Client', 'find_client_by_id', [ 'query' => [ 'id' => $report->getClient() ]]);
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @Route("/add-contact/{reportId}")
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
        return [ ];
    }
    
    /**
     * @Route("/decision/{reportId}", name="add_decision")
     * @Template("AppBundle:Decision:list.html.twig")
     */
    public function addDecisionAction($reportId)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        // just needed for title etc,
        $report = $apiClient->getEntity('Report', 'find_report_by_id', [ 'query' => [ 'id' => $reportId ]]);
        $decision = new Decision;
        $decision->setReportId($reportId);
        
        $form = $this->createForm(new FormDir\DecisionType([
            'clientInvolvedBooleanEmptyValue' => $this->get('translator')->trans('clientInvolvedBoolean.defaultOption', [], 'decision')
        ]), $decision);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add decision
                $response = $apiClient->postC('add_decision', $form->getData());
                
                return $this->redirect($this->generateUrl('add_decision', ['reportId'=>$reportId]));
            }
        }

        return [
            'decisions' => $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'query' => [ 'reportId' => $reportId ]]),
            'form' => $form->createView(),
            'report' => $report,
            'client' => [], //to pass
        ];
    }
   
    
}
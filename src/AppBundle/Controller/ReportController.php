<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        $report = new EntityDir\Report();
        $report->setClient($client->getId());
        
        $reports = $client->getReports();
        
        if(empty($reports)){
            $report->setStartDate($client->getCourtDate());
        }
        
        //if client has property & affairs and health & welfare then give them property & affairs
        //else give them health and welfare
        if(count($allowedCourtOrderTypes) > 1){
            $report->setCourtOrderType(EntityDir\Report::PROPERTY_AND_AFFAIRS);
        }else{
            $report->setCourtOrderType($allowedCourtOrderTypes[0]);
        }
        
        $form = $this->createForm(new FormDir\ReportType(), $report,
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
     * @Route("/{reportId}/overview", name="report_overview")
     * @Template()
     */
    public function overviewAction($reportId)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        $report = $apiClient->getEntity('Report', 'find_report_by_id', [ 'query' => [ 'id' => $reportId ]]);
        $client = $apiClient->getEntity('Client', 'find_client_by_id', [ 'query' => [ 'id' => $report->getClient() ]]);
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @Route("/{reportId}/contacts", name="contacts")
     * @Template()
     */
    public function contactsAction($reportId)
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
                return $this->redirect($this->generateUrl('contacts', [ 'reportId' => $reportId ]));
            }
        }
        return [ 'form' => $form->createView() ];
    }
    
}
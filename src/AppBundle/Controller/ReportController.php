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
        return [];
    }
    
    /**
     * @Route("/add-contact")
     * @Template()
     */
    public function addContactAction()
    {
        $request = $this->getRequest();
        
        $contact = new EntityDir\Contact();
        
        $form = $this->createForm(new FormDir\ContactType(), $contact);
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                print_r($form->getData()); die;
            }
        }
        return [ 'form' => $form->createView() ];
    }
}
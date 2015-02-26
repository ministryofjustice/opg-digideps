<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ReportType;
use AppBundle\Entity\Report;

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
        
        //lets check if this  user already has another report, if not start date should be court order date
        $report = new Report();
        $report->setClient($client->getId());
        
        $reports = $client->getReports();
        
        if(empty($reports)){
            $report->setStartDate($client->getCourtDate());
        }
        
        $form = $this->createForm(new ReportType($this->get('util'), $client->getAllowedCourtOrderTypes()), $report,
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
    public function overviewAction()
    {
        return [];
    }
}
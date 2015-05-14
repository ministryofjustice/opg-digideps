<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Client;
use AppBundle\Service\ApiClient;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;


class ReportController extends Controller
{
    /**
     * @Route("/report/create/{clientId}", name="report_create")
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
                return $this->redirect($this->generateUrl('report_overview', [ 'reportId' => $response['report'] ]));
            }
        }
        return [ 'form' => $form->createView() ];
    }
    
    /**
     * @Route("/report/{reportId}/overview", name="report_overview")
     * @Template()
     */
    public function overviewAction($reportId)
    {
        $report = $this->getReport($reportId);
        
        $client = $this->getClient($report->getClient());
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->isReportSubmitted($report)) {
            return $redirectResponse;
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }
    
    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $util = $this->get('util');
        $report = $util->getReport($reportId, $this->getUser()->getId());
        if (!$report->isDue()) {
            throw new \RuntimeException("Report not ready for submission.");
        }
        $clients = $this->getUser()->getClients();
        $client = $clients[0];
        
        $form = $this->createForm(new FormDir\ReportDeclarationType());
        $form->handleRequest($request);
        if($form->isValid()){
            
            /**
             * //TODO
             * 
             * ADD REAL SUBMISSION OR SENDIN HERE
             * 
             */
            
            $request->getSession()->getFlashBag()->add(
                'notice', 
                $this->get('translator')->trans('page.reportSubmittedFlashMessage', [], 'report-declaration')
            );
            return $this->redirect($this->generateUrl('report_overview', ['reportId'=>$reportId]));
        }
        
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
        ];
    }
    
    /**
     * @Route("/report/{reportId}/display", name="report_display")
     * @Template()
     */
    public function displayAction(Request $request, $reportId)
    {
        $apiClient = $this->get('apiclient');
        
        $report = $this->getReport($reportId);
        
        $client = $this->getClient($report->getClient());
        
        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'parameters' => ['id' => $reportId ]]);
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'parameters' => ['id' => $reportId ]]);
        
        return [
            'report' => $report,
            'client' => $client,
            'assets' => $assets,
            'contacts' => $contacts,
            'deputy' => $this->getUser(),
        ];
    }
    
    /**
     * @param integer $clientId
     *
     * @return Client
     */
    protected function getClient($clientId)
    {
        return $this->get('apiclient')->getEntity('Client','find_client_by_id', [ 'parameters' => [ 'id' => $clientId ]]);
    }
    
    /**
     * @param integer $reportId
     * 
     * @return Report
     */
    protected function getReport($reportId,array $groups = [ 'transactions'])
    {
        return $this->get('apiclient')->getEntity('Report', 'find_report_by_id', [ 'parameters' => [ 'userId' => $this->getUser()->getId() ,'id' => $reportId ], 'query' => [ 'groups' => $groups ]]);
    }
}
<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
        $report = $this->getReport($reportId, $this->getUser()->getId());
        $client = $this->getClient($report->getClient());

        if($report->getCourtOrderType() == EntityDir\Report::PROPERTY_AND_AFFAIRS){
            $apiClient = $this->get('apiclient');
            $accounts = $apiClient->getEntities('Account', 'get_report_accounts', [ 'query' => ['id' => $reportId, 'group' => 'transactions']]);
            $report->setAccounts($accounts);
        }
        
        return [
            'report' => $report,
            'client' => $client,
        ];
    }
    
    /**
     * @Route("/report/{reportId}/contacts/{action}", name="contacts", defaults={ "action" = "list"})
     * @Template()
     */
    public function contactsAction($reportId,$action)
    {
        $report = $this->getReport($reportId);
        $client = $this->getClient($report->getClient());

        $request = $this->getRequest();
        
        $apiClient = $this->get('apiclient');
        $contacts = $apiClient->getEntities('Contact','get_report_contacts', [ 'query' => ['id' => $reportId ]]);
        
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
        
        return [
            'form' => $form->createView(),
            'contacts' => $contacts,
            'action' => $action,
            'report' => $report,
            'client' => $client];
    }
    
  
    /**
     * @Route("/report/{reportId}/decisions/{action}", name="decisions", defaults={ "action" = "list"})
     * @Template()
     */
    public function decisionsAction($reportId,$action)
    {
        $request = $this->getRequest();
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        // just needed for title etc,
        $report = $this->getReport($reportId);
        $decision = new EntityDir\Decision;
        $decision->setReportId($reportId);
        $decision->setReport($report);
        
        $form = $this->createForm(new FormDir\DecisionType([
            'clientInvolvedBooleanEmptyValue' => $this->get('translator')->trans('clientInvolvedBoolean.defaultOption', [], 'report-decisions')
        ]), $decision);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add decision
                $apiClient->postC('add_decision', $form->getData());
                
                return $this->redirect($this->generateUrl('decisions', ['reportId'=>$reportId]));
            }
        }
        
        return [
            'decisions' => $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'query' => [ 'reportId' => $reportId ]]),
            'form' => $form->createView(),
            'report' => $report,
            'client' => $this->getClient($report->getClient()),
            'action' => $action
        ];
    }
    
    /**
     * @Route("/report/{reportId}/assets/{action}", name="assets", defaults={ "action" = "list"})
     * @Template()
     */
    public function assetsAction($reportId, $action)
    {
        $util = $this->get('util');
        $translator =  $this->get('translator');
        $dropdownKeys = $this->container->getParameter('asset_dropdown');
        $apiClient = $this->get('apiclient');
        $request = $this->getRequest();
        
        $titles = [];
        
        foreach($dropdownKeys as $key ){
            $translation = $translator->trans($key,[],'report-assets');
            $titles[$translation] = $translation;
        }

        $other = $titles['Other assets'];
        unset($titles['Other assets']);
        
        asort($titles);
        $titles['Other assets'] = $other;
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient());

        $asset = new EntityDir\Asset();
        
        $form = $this->createForm(new FormDir\AssetType($titles),$asset);

        $assets = $apiClient->getEntities('Asset','get_report_assets', [ 'query' => ['id' => $reportId ]]);
        
        if($request->getMethod() == 'POST'){
            $form->handleRequest($request);

            if($form->isValid()){
                $asset = $form->getData();
                $asset->setReport($reportId);

                $apiClient->postC('add_report_asset', $asset);
                return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId ]));
            }
        }

        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form'   => $form->createView(),
            'assets' => $assets
        ];
    }

    
    /**
     * @Route("/report/{reportId}/declaration", name="report_declaration")
     * @Template()
     */
    public function declarationAction(Request $request, $reportId)
    {
        $util = $this->get('util');
        $report = $this->getReport($reportId);
        if (!$report->isDue()) {
            throw new \RuntimeException("Report not ready for submission.");
        }
        $client = $util->getClient($report->getClient());
        
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
                $this->get('translator')->trans('page.reportSubmittedFlashMessage', [], 'report_declaration')
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
     * @param integer $clientId
     *
     * @return Client
     */
    protected function getClient($clientId)
    {
        return $this->get('apiclient')->getEntity('Client','find_client_by_id', [ 'query' => [ 'id' => $clientId ]]);
    }
    
    /**
     * @param integer $reportId
     * 
     * @return Report
     */
    protected function getReport($reportId)
    {
        return $this->get('apiclient')->getEntity('Report', 'find_report_by_id', [ 'query' => [ 'userId' => $this->getUser()->getId() ,'id' => $reportId ]]);
    }
}
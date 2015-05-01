<?php
namespace AppBundle\Controller;

use AppBundle\Form\ReportType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form\ClientType;
use AppBundle\Entity\Client;
use AppBundle\Entity as EntityDir;


/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/show/{action}", name="client_home", defaults={ "action" = ""})
     * @Template()
     */
    public function indexAction($action)
    {
        $util = $this->get('util');
        $clients = $this->getUser()->getClients();
        $request = $this->getRequest();
       
        $client = !empty($clients)? $clients[0]: null;
        
        $reportIds = $client->getReports();
        $reports = [];
        
        if(!empty($reportIds)){
            foreach($reportIds as $id){
                $reports[$id] = $util->getReport($id,$this->getUser()->getId(),[ 'basic' ]);
            }
        }

        $report = new EntityDir\Report();
        $report->setClient($client->getId());

        $formClientNewReport = $this->createForm(new ReportType(), $report);
        $formClientEditReportPeriod = $this->createForm(new ReportType(), $report);
        $clientForm = $this->createForm(new ClientType($util), $client, [ 'action' => $this->generateUrl('client_home', [ 'action' => 'edit-client'])]);
        
        if($request->getMethod() == "POST"){
            $clientForm->handleRequest($request);
            
            if($clientForm->isValid()){
                $apiClient = $this->get('apiclient');
                $clientUpdated = $clientForm->getData();
                
                $apiClient->putC('update_client', $clientUpdated);
                
                return $this->redirect($this->generateUrl('client_home'));
            }
        }
        
        return [
            'client' => $client,
            'reports' => $reports,
            'action' => $action,
            'formEditClient' => $clientForm->createView(),
            'formClientNewReport' => $formClientNewReport->createView(),
            'formClientEditReportPeriod' => $formClientEditReportPeriod->createView(),
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn')
        ];

    }
    
    /**
     * @Route("/add", name="client_add")
     * @Template()
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $util = $this->get('util');
        $apiClient = $this->get('apiclient');
        
        $client = new Client();
        $client->addUser($this->getUser()->getId());
  
        $form = $this->createForm(new ClientType($util), $client);
        
        $form->handleRequest($request);
        
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $response = $apiClient->postC('add_client', $form->getData());
               
                return $this->redirect($this->generateUrl('report_create', [ 'clientId' => $response['id'] ]));
            }
        }
        return [ 'form' => $form->createView() ];
    }
}
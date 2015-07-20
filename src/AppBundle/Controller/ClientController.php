<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Route("/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/show/{action}/{reportId}", name="client_home", defaults={ "action" = "show", "reportId" = " "})
     * @Template()
     */
    public function indexAction($action, $reportId)
    {   
        $util = $this->get('util');  /* @var $util \AppBundle\Service\Util */
        $apiClient = $this->get('apiclient');
        
        $clients = $this->getUser()->getClients(); 
        $request = $this->getRequest();
        
        $client = !empty($clients)? $clients[0]: null;
        
        $reports = $client ? $util->getReportsIndexedById($this->getUser()->getId(), $client, ['basic']) : [];
       
        $report = new EntityDir\Report();
        $report->setClient($client->getId());

        $formClientNewReport = $this->createForm(new FormDir\ReportType(), $report);
        $formClientEditReportPeriod = $this->createForm(new FormDir\ReportType(), $report);
        $clientForm = $this->createForm(new FormDir\ClientType($util), $client, [ 'action' => $this->generateUrl('client_home', [ 'action' => 'edit-client'])]);
        
        $clientForm->handleRequest($request);
        
        // edit client form
        if ($clientForm->isValid()) {
            $clientUpdated = $clientForm->getData();
            $apiClient->putC('update_client', $clientUpdated);

            return $this->redirect($this->generateUrl('client_home'));
        }
        
        // edit report dates
        if ($action == 'edit-report' && $reportId) {
            $report = $util->getReport($reportId, $this->getUser()->getId());
            $editReportDatesForm = $this->createForm(new FormDir\ReportType('report_edit'), $report, [
                'translation_domain' => 'report-edit-dates'
            ]);
            $editReportDatesForm->handleRequest($request);
            if ($editReportDatesForm->isValid()) {
                $apiClient->putC('report/' . $reportId, $report, [
                     'deserialise_group' => 'startEndDates',
                ]);
                return $this->redirect($this->generateUrl('client_home'));
            }
        }
        
        return [
            'client' => $client,
            'reports' => $reports,
            'action' => $action,
            'reportId' => $reportId,
            'editReportDatesForm' => ($action == 'edit-report') ? $editReportDatesForm->createView() : null,
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
        
        $clients = $this->getUser()->getClients();
        if (!empty($clients) && $clients[0] instanceof EntityDir\Client) {
            // update existing client
            $method = 'put';
            $client = $clients[0]; //existing client
        } else {
            // new client
            $method = 'post';
            $client = new EntityDir\Client();
            $client->addUser($this->getUser()->getId());
        }
  
        $form = $this->createForm(new FormDir\ClientType($util), $client);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = ($method === 'post') 
                      ? $apiClient->postC('add_client', $form->getData())
                      : $apiClient->putC('add_client', $form->getData());

            return $this->redirect($this->generateUrl('report_create', [ 'clientId' => $response['id'], 'registrationStep' => true ]));
        }
        return [ 'form' => $form->createView() ];
    }
}
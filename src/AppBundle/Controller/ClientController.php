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

        $formClientNewReport = $this->createForm(new ReportType(), $report);
        $formClientEditReportPeriod = $this->createForm(new ReportType(), $report);
        $clientForm = $this->createForm(new ClientType($util), $client, [ 'action' => $this->generateUrl('client_home', [ 'action' => 'edit-client'])]);
        
        $clientForm->handleRequest($request);

        if ($clientForm->isValid()) {
            $clientUpdated = $clientForm->getData();
            $apiClient->putC('update_client', $clientUpdated);

            return $this->redirect($this->generateUrl('client_home'));
        }
        
        $reportEditDatesForm = null;
        if ($action == 'edit-report' && $reportId) {
            $report = $util->getReport($reportId, $this->getUser()->getId());
            $reportEditDatesForm = $this->createForm(new ReportType(), $report, [
                'translation_domain' => 'report-edit-dates'
            ]);
            $reportEditDatesForm->handleRequest($request);
            if ($reportEditDatesForm->isValid()) {
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
            'reportEditDatesForm' => $reportEditDatesForm ? $reportEditDatesForm->createView() : null,
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
        if ($form->isValid()) {
            $response = $apiClient->postC('add_client', $form->getData());

            return $this->redirect($this->generateUrl('report_create', [ 'clientId' => $response['id'] ]));
        }
        return [ 'form' => $form->createView() ];
    }
}
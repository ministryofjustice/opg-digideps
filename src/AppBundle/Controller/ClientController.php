<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/client")
 */
class ClientController extends AbstractController
{
    /**
     * @Route("/show/{action}", name="client_home", defaults={ "action" = "show"})
     * @Template()
     */
    public function indexAction($action)
    {   
        $restClient = $this->get('restClient');
        
        $clients = $this->getUser()->getClients(); 
        $request = $this->getRequest();
        
        $client = !empty($clients)? $clients[0]: null;
        
        $reports = $client ? $this->getReportsIndexedById($client, ['basic']) : [];
        arsort($reports);
        
        $report = new EntityDir\Report();
        $report->setClient($client);

        $formClientNewReport = $this->createForm(new FormDir\ReportType(), $report);
        $formClientEditReportPeriod = $this->createForm(new FormDir\ReportType(), $report);
        $allowedCot = $this->getAllowedCourtOrderTypeChoiceOptions([], 'arsort');
        $clientForm = $this->createForm(new FormDir\ClientType($allowedCot), $client, [ 'action' => $this->generateUrl('client_home', [ 'action' => 'edit'])]);
        $clientForm->handleRequest($request);
        
        // edit client form
        if ($clientForm->isValid()) {
            $clientUpdated = $clientForm->getData();
            $clientUpdated->setId($client->getId());
            $restClient->put('client/upsert', $clientUpdated);

            return $this->redirect($this->generateUrl('client_home'));
        }
        
        return [
            'hideForm' => ($action != 'edit'),
            'formClass' => ($action == 'edit') ? 'in-page-form' : '',
            'action' => $action,
            'client' => $client,
            'formEditClient' => $clientForm->createView(),
            'formClientNewReport' => $formClientNewReport->createView(),
            'formClientEditReportPeriod' => $formClientEditReportPeriod->createView(),
            'lastSignedIn' => $this->getRequest()->getSession()->get('lastLoggedIn'),
        ];

    }
    
    /**
     * @Route("/add", name="client_add")
     * @Template()
     */
    public function addAction()
    {
        $request = $this->getRequest();
        $restClient = $this->get('restClient');
        
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
  
        $allowedCot = $this->getAllowedCourtOrderTypeChoiceOptions([], 'arsort');
        $form = $this->createForm(new FormDir\ClientType($allowedCot), $client);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            $response = ($method === 'post') 
                      ? $restClient->post('client/upsert', $form->getData())
                      : $restClient->put('client/upsert', $form->getData());

            return $this->redirect($this->generateUrl('report_create', [ 'clientId' => $response['id']]));
        }
        return [ 'form' => $form->createView() ];
    }
}
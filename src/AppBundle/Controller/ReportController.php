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
        
        $client = $this->get('apiclient')->getEntity('Client','find_client_by_id', [ 'query' => [ 'id' => $clientId ]]);
        print_r($client); die;
        $form = $this->createForm(new ReportType($this->get('util'), $client->getAllowedCourtOrderTypes()), new Report(),
                                  [ 'action' => $this->generateUrl('report_create', [ 'clientId' => $clientId ])]);
        
        //lets check if this  user already has another report, if not start date should be court order date
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                print_r($form->getData()); die('sfsdfds');
            }
        }
        return [ 'form' => $form->createView() ];
    }
}
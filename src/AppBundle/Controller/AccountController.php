<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form as FormDir;


class AccountController extends Controller
{
    /**
     * @Route("/report/{reportId}/accounts/{action}", name="accounts", defaults={ "action" = "list"})
     * @Template()
     */
    public function accountsAction($reportId,$action)
    {
        $util = $this->get('util');
        $request = $this->getRequest();
         
        $report = $util->getReport($reportId);
        $client = $util->getClient($report->getClient());
        $accounts = [];
        
        $form = $this->createForm(new FormDir\AccountType());
        $form->handleRequest($request);
        
        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form' => $form->createView(),
            'accounts' => $accounts
        ];
    }
}
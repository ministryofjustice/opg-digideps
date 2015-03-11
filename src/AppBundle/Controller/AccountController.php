<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;
use Symfony\Component\Form\FormError;


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
        
        $apiClient = $this->get('apiclient');
        $accounts = $apiClient->getEntities('Account','get_report_accounts', [ 'query' => ['id' => $reportId ]]);
        //print_r($accounts); die;
        $form = $this->createForm(new FormDir\AccountType(), new EntityDir\Account());
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $account = $form->getData();
                $account->setReport($reportId);
                
                $apiClient->postC('add_report_account', $account);
                return $this->redirect($this->generateUrl('accounts', [ 'reportId' => $reportId ]));
            }
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form' => $form->createView(),
            'accounts' => $accounts
        ];
    }
}
<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;
use Symfony\Component\Form\FormError;
use AppBundle\Service\ApiClient;


class AccountController extends Controller
{
    /**
     * @Route("/report/{reportId}/accounts/{action}", name="accounts", defaults={ "action" = "list"}, requirements={
     *   "action" = "(add|list)"
     * })
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
       
        $account = new EntityDir\Account();
        $account->setReportObject($report);
        
        $form = $this->createForm(new FormDir\AccountType(), $account);
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
    
    
    /**
     * @Route("/report/{reportId}/account/{accountId}", name="account", requirements={
     *   "accountId" = "\d+"
     * })
     * @Template()
     */
    public function accountAction($reportId, $accountId, $action = 'list')
    {
        $util = $this->get('util');
        $request = $this->getRequest();
         
        $report = $util->getReport($reportId);
        $client = $util->getClient($report->getClient());
        
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'query' => ['id' => $accountId, 'group' => 'transactions' ]]);

        $form = $this->createForm(new FormDir\AccountTransactionsType(), $account);
        
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $this->debugFormData($form);
            
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
            'account' => $account
        ];
    }
    
    private function debugFormData($form)
    {
        echo "<pre>";
        if (!$form->isValid()) {
            echo $form->getErrorsAsString();
            die;
        }
        $account = $form->getData();

        $context = \JMS\Serializer\SerializationContext::create()
                ->setSerializeNull(true)
                ->setGroups('transactions');
        
        $data = $this->get('jms_serializer')->serialize($account, 'json', $context);

        echo "data passed to API: " . print_r(json_decode($data, 1), 1);die;
    }
    
}
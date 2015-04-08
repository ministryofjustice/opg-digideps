<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;
use Symfony\Component\Form\FormError;
use AppBundle\Service\ApiClient;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends Controller
{

    /**
     * @Route("/report/{reportId}/accounts/{action}", name="accounts", defaults={ "action" = "list"}, requirements={
     *   "action" = "(add|list)"
     * })
     * @Template()
     */
    public function accountsAction($reportId, $action)
    {
        $util = $this->get('util');
        $request = $this->getRequest();
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient());

        $apiClient = $this->get('apiclient');
        $accounts = $apiClient->getEntities('Account', 'get_report_accounts', [ 'query' => ['id' => $reportId, 'group' => 'basic']]);

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\AccountType(), $account);
        $reportSubmit = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));
        
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $reportSubmit->handleRequest($request);
            
            if($form->get('save')->isClicked()){
                if ($form->isValid()) {
                    $account = $form->getData();
                    $account->setReport($reportId);

                    $response = $apiClient->postC('add_report_account', $account);
                    return $this->redirect(
                        $this->generateUrl('account', [ 'reportId' => $reportId, 'accountId'=>$response['id'] ])
                    );
                } else {
                    echo $form->getErrorsAsString();
                }
            }else{
                $checkArray = $reportSubmit->get('reviewed_n_checked')->getData();
         
                if(!empty($checkArray)){
                    if(!$report->readyToSubmit()){
                        return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
                    }
                }
            }
        }

        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form' => $form->createView(),
            'accounts' => $accounts,
            'report_form_submit' => $reportSubmit->createView()
        ];
    }

    /**
     * Single account page
     * - money in/out
     * - account closing balance
     * 
     * @Route("/report/{reportId}/account/{accountId}/{action}", name="account", requirements={
     *   "accountId" = "\d+",
     *   "action" = "[\w-]*"
     * }, defaults={ "action" = "list"})
     * @Template()
     */
    public function accountAction($reportId, $accountId, $action)
    {
        $util = $this->get('util');

        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient());

        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'query' => ['id' => $accountId, 'group' => 'transactions']]);
        $account->setReportObject($report);
        
        // closing balance logic
        list($formBalance, $validFormBalance) = $this->handleClosingBalance($account);
        if ($validFormBalance) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formBalance->getData(), [
                'deserialise_group' => 'balance',
            ]);
            return $this->redirect($this->generateUrl('account', [ 'reportId' => $account->getReportObject()->getId(), 'accountId'=>$account->getId() ]) . '#closing-balance');
        }
        
        // money in/out logic
        list($formMoneyInOut, $formMoneyValid) = $this->handleMoneyInOut($account);
        if ($formMoneyValid) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formMoneyInOut->getData(), [
                'deserialise_group' => 'transactions',
            ]);
        }
        
        // refresh account data
        if ($validFormBalance || $formMoneyValid) {
            $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'query' => ['id' => $accountId, 'group' => 'transactions']]);
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $formMoneyInOut->createView(),
            'formBalance' => $formBalance->createView(),
            'account' => $account,
            'actionParam' => $action,
        ];
    }
    
    
    /**
     * @param EntityDir\Account $account
     * 
     * @return [FormDir\AccountTransactionsType, boolean]
     */
    private function handleClosingBalance(EntityDir\Account $account)
    {
        $form = $this->createForm(new FormDir\AccountBalanceType(), $account);
        $form->handleRequest($this->getRequest());
        $isClicked = $form->get('save')->isClicked();
        $valid = $isClicked && $form->isValid();
        
        return [$form, $valid];
    }
    
    
    /**
     * @param EntityDir\Account $account
     * 
     * @return [FormDir\AccountTransactionsType, boolean]
     */
    private function handleMoneyInOut(EntityDir\Account $account)
    {
        $form = $this->createForm(new FormDir\AccountTransactionsType(), $account, [
            'action' => $this->generateUrl('account', [ 'reportId' => $account->getReportObject()->getId(), 'accountId'=>$account->getId() ]) . '#account-header'
        ]);
        $form->handleRequest($this->getRequest());
        $isClicked = $form->get('saveMoneyIn')->isClicked() || $form->get('saveMoneyOut')->isClicked();
        $valid = $isClicked && $form->isValid();
        
        return [$form, $valid];
    }
}
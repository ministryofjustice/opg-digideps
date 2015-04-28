<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Form as FormDir;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\ApiClient;

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
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $request = $this->getRequest();
        
        $report = $util->getReport($reportId, $this->getUser()->getId());
        $client = $util->getClient($report->getClient());

        $accounts = $report->getAccounts();

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
                }
            }else{
         
                if($reportSubmit->isValid()){
                    if($report->readyToSubmit()){
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
        
        $reportSubmit = $this->createForm(new FormDir\ReportSubmitType($this->get('translator')));

        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'parameters' => ['id' => $accountId ], 'query' => [ 'groups' => [ 'transactions' ]]]);
        
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
        
        // report submit logic
        $reportSubmit->handleRequest($this->getRequest());
        if ($reportSubmit->get('submitReport')->isClicked() 
            && $reportSubmit->isValid() 
            && $report->readyToSubmit()
        ){
            return $this->redirect($this->generateUrl('report_declaration', [ 'reportId' => $report->getId() ]));
        }
        
        // edit details logic
        list($formEdit, $formEditValid) = $this->handleAccountEditing($account);
        if($formEdit->get('save')->isClicked() && ($formEditValid = $formEdit->isValid()) ){
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formBalance->getData(), [
                'deserialise_group' => 'edit_account', //TODO implement group (all except Id) ?
            ]);
        }
        
        // refresh account data after if any form is successful
        if ($validFormBalance || $formMoneyValid || $formEditValid) {
            $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'parameters' => ['id' => $accountId ], 'query' => [ 'groups' => 'transactions']]);
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $formMoneyInOut->createView(),
            'formBalance' => $formBalance->createView(),
            'formEdit' => $formEdit ? $formEdit->createView() : null,
            'account' => $account,
            'actionParam' => $action,
            'report_form_submit' => $reportSubmit->createView()
        ];
    }
    
    
    /**
     * @param EntityDir\Account $account
     * 
     * @return [FormDir\AccountTransactionsType, boolean]
     */
    private function handleAccountEditing(EntityDir\Account $account)
    {
        $form = $this->createForm(new FormDir\AccountType(), $account, [
            'addClosingBalance' => $account->getReportObject()->isDue()
        ]);
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
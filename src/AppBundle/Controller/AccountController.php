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
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\AccountType(), $account);
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }
        
        $form->handleRequest($request);
        if ($form->get('save')->isClicked() && $form->isValid()) {
            $account = $form->getData();
            $account->setReport($reportId);

            $response = $apiClient->postC('add_report_account', $account, [
                'deserialise_group' => 'add'
            ]);
            return $this->redirect(
                $this->generateUrl('account', [ 'reportId' => $reportId, 'accountId'=>$response['id'] ])
            );
        }

        return [
            'report' => $report,
            'client' => $client,
            'action' => $action,
            'form' => $form->createView(),
            'accounts' => $accounts,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }

    /**
     * Single account page
     * - money in/out
     * - account closing balance
     * 
     * @Route("/report/{reportId}/account/{accountId}/{action}", name="account", requirements={
     *   "accountId" = "\d+",
     *   "action" = "edit|delete|money-in|money-out|money-both|list"
     * }, defaults={ "action" = "list"})
     * @Template()
     */
    public function accountAction($reportId, $accountId, $action)
    {
        $util = $this->get('util');

        $report = $util->getReport($reportId, $this->getUser()->getId());
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $util->getClient($report->getClient());

        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'parameters' => ['id' => $accountId ], 'query' => [ 'groups' => [ 'transactions' ]]]);
        
        $account->setReportObject($report);
        
        $edifFormHasClosingBalance = $report->isDue() && $account->getClosingBalance() > 0;
        
        // closing balance logic
        list($formBalance, $formBalanceIsSubmitted, $validFormBalance) = $this->handleClosingBalanceForm($account);
        if ($validFormBalance) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formBalance->getData(), [
                'deserialise_group' => 'balance',
            ]);
            return $this->redirect($this->generateUrl('account', [ 'reportId' => $account->getReportObject()->getId(), 'accountId'=>$account->getId() ]) . '#closing-balance');
        }
        
        // money in/out logic
        list($formMoneyInOut, $formMoneyValid) = $this->handleMoneyInOutForm($account);
        if ($formMoneyValid) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formMoneyInOut->getData(), [
                'deserialise_group' => 'transactions',
            ]);
        }
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }
        
        // edit/delete logic
        list($formEdit, $isEdit, $isDelete) = $this->handleAccountEditDeleteForm($account, [
            'showClosingBalance' => $edifFormHasClosingBalance,
            'showSubmitButton' => $action != 'delete',
            'showDeleteButton' => $action == 'delete'
        ]);
        if ($isEdit) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formBalance->getData(), [
                'deserialise_group' => $edifFormHasClosingBalance ? 'edit_details_report_due' : 'edit_details',
            ]);
            return $this->redirect($this->generateUrl('account', [ 'reportId' => $account->getReportObject()->getId(), 'accountId'=>$account->getId() ]));
        } else if ($isDelete) {
            $this->get('apiclient')->delete('account/' .  $account->getId());
            return $this->redirect($this->generateUrl('accounts', [ 'reportId' => $report->getId()]));
        }
        
        $refreshedAccount = $apiClient->getEntity('Account', 'find_account_by_id', [ 'parameters' => ['id' => $accountId ], 'query' => [ 'groups' => 'transactions']]);
        $refreshedAccount->setReportObject($report);
        
        // refresh account data after forms have altered the account's data
        if ($validFormBalance || $formMoneyValid || $isEdit) {
            $account = $refreshedAccount;
        }
        
        $formBalanceShow = $action == 'list' && $report->isDue() && !$refreshedAccount->isClosingBalanceAndDateValid();
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $formMoneyInOut->createView(),
            'formBalance' => $formBalance->createView(),
            // if report is due and the closing balance is not set, show the closing balance form
            'formBalanceOptions' => [
                'showForm' => $formBalanceShow,
                'closingDateExplanation' => ['show' => $formBalanceIsSubmitted && !$account->isClosingDateValid()],
                'closingBalanceExplanation' => ['show' => $formBalanceIsSubmitted && !$account->isClosingBalanceValid()],
            ],
            'formEdit' => $formEdit ? $formEdit->createView() : null,
            'showEditForm' => $action == 'edit' || $action == 'delete',
            'showDeleteConfirmation' => $action == 'delete',
            'account' => $account,
            'actionParam' => $action,
            'report_form_submit' => $this->get('reportSubmitter')->getFormView()
        ];
    }
    
    /**
     * @param EntityDir\Account $account
     * 
     * @return [FormDir\AccountTransactionsType, boolean]
     */
    private function handleAccountEditDeleteForm(EntityDir\Account $account, array $options)
    {
        $form = $this->createForm(new FormDir\AccountType($options), $account);
        $form->handleRequest($this->getRequest());
        $isEdit = $form->has('save') && $form->get('save')->isClicked() && $form->isValid();
        $isDelete = $form->has('delete') && $form->get('delete')->isClicked();
        
        return [$form, $isEdit, $isDelete];
    }
    
    
    /**
     * @param EntityDir\Account $account
     * 
     * @return [FormDir\AccountTransactionsType, boolean, boolean]
     */
    private function handleClosingBalanceForm(EntityDir\Account $account)
    {
        $form = $this->createForm(new FormDir\AccountClosingBalanceType(), $account);
        $form->handleRequest($this->getRequest());
        $isClicked = $form->get('save')->isClicked();
        $valid = $isClicked && $form->isValid();
        
        return [$form, $isClicked, $valid];
    }
    
    
    /**
     * @param EntityDir\Account $account
     * 
     * @return [FormDir\AccountTransactionsType, boolean]
     */
    private function handleMoneyInOutForm(EntityDir\Account $account)
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
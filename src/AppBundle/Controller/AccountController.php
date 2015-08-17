<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\ApiClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\AccountType(), $account, [
            'action' => $this->generateUrl('accounts', [ 'reportId' => $reportId, 'action'=>'add' ]) . "#pageBody"
        ]);
        
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
            
            $request->getSession()->getFlashBag()->add(
                'action', 
                'page.accountAdded'
            );

            return $this->redirect(
                $this->generateUrl('accounts', [ 'reportId' => $reportId ]) . "#pageBody"
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
        if (!in_array($accountId, $report->getAccountIds())) {
            throw new \RuntimeException("Bank account not found.");
        }
        $client = $util->getClient($report->getClient(), $this->getUser()->getId());

        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'parameters' => ['id' => $accountId ], 'query' => [ 'groups' => [ 'transactions' ]]]);
        $account->setReportObject($report);
        
        // closing balance logic
        list($formClosingBalance, $closingBalanceFormIsSubmitted, $formBalanceIsValid) = $this->handleClosingBalanceForm($account);
        if ($action == "list") {
            if ($formBalanceIsValid) {
                $this->get('apiclient')->putC('account/' .  $account->getId(), $formClosingBalance->getData(), [
                    'deserialise_group' => 'balance',
                ]);

                return $this->redirect($this->generateUrl('account', [ 'reportId' => $account->getReportObject()->getId(), 'accountId'=>$account->getId() ]) . '#closing-balance');
            }
        }
        
        // money in/out logic
        list($formMoneyInOut, $formMoneyIsValid) = $this->handleMoneyInOutForm($account);
        if ($formMoneyIsValid) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formMoneyInOut->getData(), [
                'deserialise_group' => 'transactions',
            ]);
        }
        
        // report submit logic
        if ($redirectResponse = $this->get('reportSubmitter')->submit($report)) {
            return $redirectResponse;
        }
        
        // edit/delete logic
        $editFormHasClosingBalance = $report->isDue() && $account->hasAtLeastOneTotalOutAndIn();
        list($formEdit, $formEditIsValid, $formDeleteIsValid) = $this->handleAccountEditDeleteForm($account, [
            'showClosingBalance' => $editFormHasClosingBalance,
            'showSubmitButton' => $action != 'delete',
            'showDeleteButton' => $action == 'delete'
        ]);
        if ($formEditIsValid) {
            $this->get('apiclient')->putC('account/' .  $account->getId(), $formClosingBalance->getData(), [
                'deserialise_group' => $editFormHasClosingBalance ? 'edit_details_report_due' : 'edit_details',
            ]);
            return $this->redirect($this->generateUrl('account', [ 'reportId' => $account->getReportObject()->getId(), 'accountId'=>$account->getId() ]));
        } else if ($formDeleteIsValid) {
            $this->get('apiclient')->delete('account/' .  $account->getId());
            return $this->redirect($this->generateUrl('accounts', [ 'reportId' => $report->getId()]));
        }
        
        // get account from db
        $refreshedAccount = $apiClient->getEntity('Account', 'find_account_by_id', [ 'parameters' => ['id' => $accountId ], 'query' => [ 'groups' => 'transactions']]);
        $refreshedAccount->setReportObject($report);
        
        // refresh account data after forms have altered the account's data
        if ($formBalanceIsValid || $formMoneyIsValid || $formEditIsValid) {
            //TODO try tests without this
            $account = $refreshedAccount;
        }
        
        return [
            'report' => $report,
            'client' => $client,
            // moneyIn/Out form
            'form' => $formMoneyInOut->createView(),
            // closing balance form: 
            // Show the form if on list view, the report is due and the closing balance is not added. 
            //  also show if the form is submitted but not valid 
            'closingBalanceForm' => $formClosingBalance->createView(),
            'closingBalanceFormShow' => ($action == 'list' && $report->isDue() && $account->needsClosingBalanceData() && $account->hasAtLeastOneTotalOutAndIn()) 
                                        || ($closingBalanceFormIsSubmitted && !$formBalanceIsValid),
            'closingBalanceFormDateExplanationShow' => $account->getClosingDate() && $closingBalanceFormIsSubmitted 
                                                       && !$account->isClosingDateEqualToReportEndDate(),
            'closingBalanceFormBalanceExplanationShow' => $account->getClosingBalance() !== null && $closingBalanceFormIsSubmitted 
                                                          && !$account->isClosingBalanceMatchingTransactionSum(),
            // edit form: show closing balance/date explanation only in case of mismatch
            'formEdit' => $formEdit ? $formEdit->createView() : null,
            'formEditShow' => $action == 'edit' || $action == 'delete',
            // edit form: show closing date explanation is submitted with a value, or it's just not valid 
            'formEditClosingDateExplanationShow' => $account->getClosingDate() && !$account->isClosingDateEqualToReportEndDate(),
            'formEditClosingBalanceExplanationShow' => $account->getClosingBalance() && !$account->isClosingBalanceMatchingTransactionSum(),
            // delete forms
            'showDeleteConfirmation' => $action == 'delete',
            // other date needed for the view (list action mainly)
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
        $isEditOrAddSubmitted = $form->has('save') && $form->get('save')->isClicked();
        // set default value to make submit working when JS is not enabled
        if (!$account->getId() && $isEditOrAddSubmitted) {
             $form->get('openingDateSame')->setData(EntityDir\Account::OPENING_DATE_SAME_NO);
        }
        $isEditSubmittedAndValid = $isEditOrAddSubmitted && $form->isValid();
        $isDeleteSubmittedAndValid = $form->has('delete') && $form->get('delete')->isClicked();
        
        // account edit/add post-save logic
        //TODO refactor in PRE_SUBMIT event
        if ($form->has('save') && $form->get('save')->isClicked()) {
            // if closing date is valid, reset the explanation
            if ($account->isClosingDateEqualToReportEndDate()) {
                $account->setClosingDateExplanation(null);
            }
            // if closing balance is valid, reset the explanation
            if ($account->isClosingBalanceMatchingTransactionSum()) {
                $account->setClosingBalanceExplanation(null);
            }
        }
        
        return [$form, $isEditSubmittedAndValid, $isDeleteSubmittedAndValid];
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
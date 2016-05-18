<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class BankAccountController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/accounts/moneyin", name="accounts_moneyin")
     * @param integer $reportId
     * @param Request $request
     * @Template()
     * @return array
     */
    public function moneyinAction(Request $request, $reportId) {

        $report = $this->getReport($reportId, [ 'transactionsIn', 'basic', 'client', 'balance']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        $form = $this->createForm(new FormDir\TransactionsType('transactionsIn'), $report);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $this->get('restClient')->put('report/' .  $report->getId(), $form->getData(), [
                'deserialise_group' => 'transactionsIn',
            ]);
            
            return $this->redirect($this->generateUrl('accounts_moneyin', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'moneyin',
            'jsonEndpoint' => 'transactionsIn',
            'form' => $form->createView()
        ];
        
    }

    /**
     * @Route("/report/{reportId}/accounts/moneyout", name="accounts_moneyout")
     * @param integer $reportId
     * @param Request $request
     * @Template()
     * @return array
     */
    public function moneyoutAction(Request $request, $reportId) 
    {
        $report = $this->getReport($reportId, [ 'transactionsOut', 'basic', 'client', 'balance']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        $form = $this->createForm(new FormDir\TransactionsType('transactionsOut'), $report);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $this->get('restClient')->put('report/' .  $report->getId(), $form->getData(), [
                'deserialise_group' => 'transactionsOut',
            ]);
            return $this->redirect($this->generateUrl('accounts_moneyout', ['reportId' => $reportId]));
        }
        
        return [
            'report' => $report,
            'subsection' => "moneyout",
            'jsonEndpoint' => 'transactionsOut',
            'form' => $form->createView()
        ];
        
    }

    /**
     * @Route("/report/{reportId}/accounts/balance", name="accounts_balance")
     * @param integer $reportId
     * @Template()
     * @return array
     */
    public function balanceAction(Request $request, $reportId)
    {
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        
        $report = $this->getReport($reportId, [ 'basic', 'balance', 'client', 'transactionsIn', 'transactionsOut']);
        $accounts = $restClient->get("/report/{$reportId}/accounts", 'Account[]');
        $report->setAccounts($accounts);
        
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        $form = $this->createForm(new FormDir\ReasonForBalanceType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $data = $form->getData();
            $this->get('restClient')->put('report/' . $reportId, $data, [
                'deserialise_group' => 'balance_mismatch_explanation'
            ]);
        }
        
        return [
            'report' => $report,
            'form' => $form->createView(),
            'subsection' => 'balance'
        ];
        
    }
    
    /**
     * @Route("/report/{reportId}/accounts", name="accounts")
     * @param integer $reportId
     * @Template()
     * @return array
     */
    public function banksAction($reportId) 
    {
        $report = $this->getReport($reportId, ['basic', 'client', 'balance', 'accounts']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        return [
            'report' => $report,
            'subsection' => 'banks'
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/banks/upsert/{id}", name="upsert_account", defaults={ "id" = null })
     * 
     * @param Request $request
     * @param integer $reportId
     * @param integer $id account Id
     * 
     * @Template()
     * @return array
     */
    public function upsertAction(Request $request, $reportId, $id = null) 
    {
        $restClient = $this->getRestClient(); /* @var $restClient RestClient */
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'accounts']);
        $type = $id ? 'edit' : 'add';
        $showMigrationWarning = false;
        
        if ($type === 'edit') {
            if (!$report->hasAaccountWithId($id)) {
                throw new \RuntimeException("Account not found."); 
            }
            $account = $restClient->get('report/account/' . $id, 'Account');
            // not existingAccount.accountNumber or (existingAccount.requiresBankNameAndSortCode and not existingAccount.sortCode)
            $showMigrationWarning = $account->hasMissingInformation();
        } else {
            $account = new EntityDir\Account();
            $account->setReport($report);
        }
        // display the checkbox if either told by the URL, or closing balance is zero, or it was previously ticked
        $showIsClosed = $request->query->get('show-is-closed') == 'yes' || $account->isClosingBalanceZero() || $account->getIsClosed();
        $form = $this->createForm(new FormDir\AccountType(), $account);
        $form->handleRequest($request);
        
        if($form->isValid()){
            $data = $form->getData();
            $data->setReport($report);
            // if closing balance is set to non-zero values, un-close the account
            if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }
            if ($type === 'edit') {
                $restClient->put('/account/' . $id, $account, [
                    'deserialise_group' => 'add_edit'
                ]);
                
            } else {
                $addedAccount = $this->get('restClient')->post('report/' . $reportId . '/account', $account, [
                    'deserialise_group' => 'add_edit'
                ]);
                $id = $addedAccount['id'];
            }
            
            // if the balance is zero, and the isClosed checkbox is not shown, redirect to the edit page with the checkbox visible
            if ($data->isClosingBalanceZero() &&
                !$showIsClosed // avoid loops    
            ) {
                return $this->redirect($this->generateUrl('upsert_account', ['reportId'=>$reportId, 'id'=>$id, 'show-is-closed'=>'yes']) . '#form-group-account_sortCode');
            }
            
            return $this->redirect($this->generateUrl('accounts', ['reportId'=>$reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'banks',
            'form' => $form->createView(),
            'type' => $type,
            'showMigrationWarning' => $showMigrationWarning,
            'account' => $account,
            'showIsClosed' => $showIsClosed == 'yes'
        ];

    }

    /**
     * @Route("/report/{reportId}/accounts/banks/{id}/delete", name="delete_account")
     * @param integer $reportId
     * @param integer $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'basic', 'client', 'accounts']);
        $restClient = $this->getRestClient(); /* @var $restClient RestClient */

        if ($report->hasAaccountWithId($id)) {
            $restClient->delete("/account/{$id}");
        }

        return $this->redirect($this->generateUrl('accounts', [ 'reportId' => $reportId ]));

    }

    /**
     * @Route("/report/{reportId}/accounts/{type}.json", name="accounts_money_save_json",
     *     requirements={"type"="transactionsIn|transactionsOut"}
     * )
     * @Method({"PUT"})
     *
     * @param Request $request
     * @param integer $reportId
     * @param string $type
     *
     * 1000 - Already submitted
     * 1001 - Form field error
     * 1002 - Exception
     * 1003 - Error saving
     * @return JsonResponse
     */
    public function moneySaveJson(Request $request, $reportId, $type)
    {
        try {
            
            $report = $this->getReport($reportId, [$type, 'basic', 'balance']);
        
            if ($report->getSubmitted()) {
                
                return new JsonResponse([
                    'success' => false, 
                    'errors' => [
                        'errorCode' => 1000,
                        'errorDescription' => "Unable to change submitted report "
                    ]
                ], 500);
                
            }

            $form = $this->createForm(new FormDir\TransactionsType($type), $report, ['method' => 'PUT']);
            $form->handleRequest($request);
            
            if (!$form->isValid()) {
                $errorsArray = $this->get('formErrorsFormatter')->toArray($form);
                return new JsonResponse([
                    'success' => false, 
                    'errors' => [
                        'errorCode' => 1001,
                        'errorDescription' => "Form validation error",
                        'fields' => $errorsArray
                    ]
                ], 500);
            }

            $this->get('restClient')->put('report/' . $report->getId(), $form->getData(), [
                'deserialise_group' => $type,
            ]);
            
            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'errorCode' => 1002,
                    'errorDescription' => $e->getMessage()
                ]
            ], 500);

        }
    }
    
}

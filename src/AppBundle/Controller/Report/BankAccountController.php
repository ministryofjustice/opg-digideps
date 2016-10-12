<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class BankAccountController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/accounts/moneyin", name="accounts_moneyin")
     *
     * @param int     $reportId
     * @param Request $request
     * @Template()
     *
     * @return array
     */
    public function moneyinAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactionsIn', 'balance']);
        $form = $this->createForm(new FormDir\Report\TransactionsType('transactionsIn'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), ['transactionsIn']);

            return $this->redirect($this->generateUrl('accounts_moneyin', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'moneyin',
            'jsonEndpoint' => 'transactionsIn',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/moneyout", name="accounts_moneyout")
     *
     * @param int     $reportId
     * @param Request $request
     * @Template()
     *
     * @return array
     */
    public function moneyoutAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactionsOut', 'balance']);

        $form = $this->createForm(new FormDir\Report\TransactionsType('transactionsOut'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), ['transactionsOut']);

            return $this->redirect($this->generateUrl('accounts_moneyout', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'moneyout',
            'jsonEndpoint' => 'transactionsOut',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/balance", name="accounts_balance")
     *
     * @param int $reportId
     * @Template()
     *
     * @return array
     */
    public function balanceAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['balance', 'account', 'transaction']);
        $form = $this->createForm(new FormDir\Report\ReasonForBalanceType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $this->getRestClient()->put('report/'.$reportId, $data, ['balance_mismatch_explanation']);
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'subsection' => 'balance',
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts", name="accounts")
     *
     * @param int $reportId
     * @Template()
     *
     * @return array
     */
    public function banksAction($reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['balance', 'account']);

        return [
            'report' => $report,
            'subsection' => 'banks',
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/banks/upsert/{id}", name="account_upsert", defaults={ "id" = null })
     * 
     * @param Request $request
     * @param int     $reportId
     * @param int     $id       account Id
     * 
     * @Template()
     *
     * @return array
     */
    public function upsertAction(Request $request, $reportId, $id = null)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactions', 'client', 'account']);
        $type = $id ? 'edit' : 'add';
        $showMigrationWarning = false;

        if ($type === 'edit') {
            if (!$report->hasAccountWithId($id)) {
                throw new \RuntimeException('Account not found.');
            }
            $account = $this->getRestClient()->get('report/account/'.$id, 'Report\\Account');
            // not existingAccount.accountNumber or (existingAccount.requiresBankNameAndSortCode and not existingAccount.sortCode)
            $showMigrationWarning = $account->hasMissingInformation();
        } else {
            $account = new EntityDir\Report\Account();
            $account->setReport($report);
        }
        // display the checkbox if either told by the URL, or closing balance is zero, or it was previously ticked
        $showIsClosed = $request->query->get('show-is-closed') == 'yes' || $account->isClosingBalanceZero() || $account->getIsClosed();
        $form = $this->createForm(new FormDir\Report\BankAccountType(), $account);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);
            // if closing balance is set to non-zero values, un-close the account
            if (!$data->isClosingBalanceZero()) {
                $data->setIsClosed(false);
            }
            if ($type === 'edit') {
                $this->getRestClient()->put('/account/'.$id, $account, ['account']);
            } else {
                $addedAccount = $this->getRestClient()->post('report/'.$reportId.'/account', $account, ['account']);
                $id = $addedAccount['id'];
            }

            // if the balance is zero, and the isClosed checkbox is not shown, redirect to the edit page with the checkbox visible
            if ($data->isClosingBalanceZero() &&
                !$showIsClosed // avoid loops    
            ) {
                return $this->redirect($this->generateUrl('account_upsert', ['reportId' => $reportId, 'id' => $id, 'show-is-closed' => 'yes']).'#form-group-account_sortCode');
            }

            return $this->redirect($this->generateUrl('accounts', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'banks',
            'form' => $form->createView(),
            'type' => $type,
            'showMigrationWarning' => $showMigrationWarning,
            'account' => $account,
            'showIsClosed' => $showIsClosed == 'yes',
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/banks/{id}/delete", name="account_delete")
     *
     * @param int $reportId
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction($reportId, $id)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account']);

        if ($report->hasAccountWithId($id)) {
            $this->getRestClient()->delete("/account/{$id}");
        }

        return $this->redirect($this->generateUrl('accounts', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/accounts/{type}.json", name="accounts_money_save_json",
     *     requirements={"type"="transactionsIn|transactionsOut"}
     * )
     * @Method({"PUT"})
     *
     * @param Request $request
     * @param int     $reportId
     * @param string  $type
     *
     * 1000 - Already submitted
     * 1001 - Form field error
     * 1002 - Exception
     * 1003 - Error saving
     *
     * @return JsonResponse
     */
    public function moneySaveJson(Request $request, $reportId, $type)
    {
        try {
            $report = $this->getReport($reportId, [$type, 'balance']);
            if ($report->getSubmitted()) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'errorCode' => 1000,
                        'errorDescription' => 'Unable to change submitted report ',
                    ],
                ], 500);
            }

            $form = $this->createForm(new FormDir\Report\TransactionsType($type), $report, ['method' => 'PUT']);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                $errorsArray = $this->get('formErrorsFormatter')->toArray($form);

                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'errorCode' => 1001,
                        'errorDescription' => 'Form validation error',
                        'fields' => $errorsArray,
                    ],
                ], 500);
            }

            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), [$type]);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'errorCode' => 1002,
                    'errorDescription' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}

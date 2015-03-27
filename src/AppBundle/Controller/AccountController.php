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
    public function accountsAction($reportId, $action)
    {
        $util = $this->get('util');
        $request = $this->getRequest();

        $report = $util->getReport($reportId);
        $client = $util->getClient($report->getClient());

        $apiClient = $this->get('apiclient');
        $accounts = $apiClient->getEntities('Account', 'get_report_accounts', [ 'query' => ['id' => $reportId, 'group' => 'basic']]);

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\AccountType(), $account);
        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
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
     * @Route("/report/{reportId}/account/{accountId}/{action}", name="account", requirements={
     *   "accountId" = "\d+",
     *   "action" = "[\w-]*"
     * }, defaults={ "action" = "list"})
     * @Template()
     */
    public function accountAction($reportId, $accountId, $action)
    {
        $util = $this->get('util');
        $request = $this->getRequest();

        $report = $util->getReport($reportId);
        $client = $util->getClient($report->getClient());

        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'query' => ['id' => $accountId, 'group' => 'transactions']]);

        $form = $this->createForm(new FormDir\AccountTransactionsType(), $account, [
            'action' => $this->generateUrl('account', [ 'reportId' => $reportId, 'accountId'=>$accountId ])
        ]);
        
        $form->handleRequest($request);
        if ($form->isValid()) {
            #$this->debugFormData($form);
            $apiClient->putC('account/' .  $account->getId(), $form->getData(), [
                'deserialise_group' => 'transactions',
            ]);
            // refresh account
            $account = $apiClient->getEntity('Account', 'find_account_by_id', [ 'query' => ['id' => $accountId, 'group' => 'transactions']]);
        }

        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
            'account' => $account,
            'actionParam' => $action,
        ];
    }
}
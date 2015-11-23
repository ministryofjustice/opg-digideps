<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Service\ReportStatusService;
use Symfony\Component\HttpFoundation\Request;


class Account2Controller extends AbstractController
{
    /**
     * @Route("/report/{reportId}/accounts/moneyin", name="accounts_moneyin")
     * @Template()
     */
    public function moneyinAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'transactionsIn', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\TransactionsType('transactionsIn'), $report);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('restClient')->put('report/' .  $report->getId(), $form->getData(), [
                'deserialise_group' => 'transactionsIn',
            ]);
            return $this->redirect($this->generateUrl('accounts_moneyin', ['reportId'=>$reportId]) );
        }

        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => 'moneyin',
            'form' => $form->createView()
        ];


    }

    /**
     * @Route("/report/{reportId}/accounts/moneyout", name="accounts_moneyout")
     * @Template()
     */
    public function moneyoutAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'transactionsOut', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\TransactionsType('transactionsOut'), $report);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('restClient')->put('report/' .  $report->getId(), $form->getData(), [
                'deserialise_group' => 'transactionsOut',
            ]);
            return $this->redirect($this->generateUrl('accounts_moneyout', ['reportId'=>$reportId]) );
        }


        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => "moneyout",
            'form' => $form->createView()
        ];
        
    }

    /**
     * @Route("/report/{reportId}/accounts/banks", name="accounts_banks")
     * @Template()
     */
    public function banksAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();
        
        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => 'banks'
        ];


    }

    /**
     * @Route("/report/{reportId}/accounts/balance", name="accounts_balance")
     * @Template()
     */
    public function balanceAction($reportId) {

        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());

        $accounts = $report->getAccounts();

        $account = new EntityDir\Account();
        $account->setReportObject($report);

        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => 'balance'
        ];


    }


    /**
     * @Route("/{reportId}/accounts", name="accounts")
     * @return RedirectResponse
     */
    public function accountsAction($reportId)
    {
        return $this->redirect($this->generateUrl('accounts_moneyin', [ 'reportId' => $reportId]));
    }

    /**
     * @Route("/{reportId}/accounts/add", name="add_bank_account")
     * @Template()      
     */
    public function addAction($reportId) {
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */
        $request = $this->getRequest();

        $report = $this->getReport($reportId, [ 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        $client = $this->getClient($report->getClient());
        
        $account = new EntityDir\Account();
        $account->setReportObject($report);

        $form = $this->createForm(new FormDir\Account2Type(), $account, [
            'action' => $this->generateUrl('accounts', [ 'reportId' => $reportId, 'action'=>'add' ]) . "#pageBody"
        ]);

        return [
            'report' => $report,
            'client' => $client,
            'subsection' => 'banks',
            'form' => $form->createView()
        ]; 
    }
    
}

<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends AbstractController
{

    /**
     * @Route("/report/{reportId}/accounts", name="accounts")
     * @param integer $reportId
     * @return RedirectResponse
     */
    public function accountsAction($reportId)
    {
        return $this->redirect($this->generateUrl('accounts_moneyin', [ 'reportId' => $reportId]));
        
        // todo Check the referrer url, if it came from adding a bank account then direct them to the bank account list instead.
    }
    
    /**
     * @Route("/report/{reportId}/accounts/moneyin", name="accounts_moneyin")
     * @param integer $reportId
     * @param Request $request
     * @Template()
     * @return array
     */
    public function moneyinAction(Request $request, $reportId) {

        $report = $this->getReport($reportId, [ 'transactionsIn', 'basic', 'balance']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        $form = $this->createForm(new FormDir\TransactionsType('transactionsIn'), $report);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $this->get('restClient')->put('report/' .  $report->getId(), $form->getData(), [
                'deserialise_group' => 'transactionsIn',
            ]);
            return $this->redirect($this->generateUrl('accounts_moneyin', ['reportId'=>$reportId]) );
        }

        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
            'subsection' => 'moneyin',
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
        $report = $this->getReport($reportId, [ 'transactionsOut', 'basic', 'balance']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        $form = $this->createForm(new FormDir\TransactionsType('transactionsOut'), $report);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $this->get('restClient')->put('report/' .  $report->getId(), $form->getData(), [
                'deserialise_group' => 'transactionsOut',
            ]);
            return $this->redirect($this->generateUrl('accounts_moneyout', ['reportId'=>$reportId]) );
        }
        
        $client = $this->getClient($report->getClient());
        return [
            'report' => $report,
            'client' => $client,
            'subsection' => "moneyout",
            'form' => $form->createView()
        ];
        
    }

    /**
     * @Route("/report/{reportId}/accounts/balance", name="accounts_balance")
     * @param integer $reportId
     * @Template()
     * @return array
     */
    public function balanceAction($reportId) 
    {
        
        $report = $this->getReport($reportId, [ 'basic', 'balance']);
        
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
     * @Route("/report/{reportId}/accounts/banks", name="accounts_banks")
     * @param integer $reportId
     * @Template()
     * @return array
     */
    public function banksAction($reportId) 
    {
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */

        $report = $this->getReport($reportId, [ 'transactions', 'basic']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }
        
        $client = $this->getClient($report->getClient());
        $accounts = $restClient->get("/report/{$reportId}/accounts", 'Account[]');
        
        return [
            'report' => $report,
            'client' => $client,
            'accounts' => $accounts,
            'subsection' => 'banks'
        ];
    }
    
    
    /**
     * @Route("/{reportId}/accounts/banks/add", name="add_account")
     * @param integer $reportId
     * @param Request $request
     * @Template()
     * @return array    
     */
    public function addAction(Request $request, $reportId) 
    {

        $report = $this->getReportIfReportNotSubmitted($reportId);

        $account = new EntityDir\Account();
        $account->setReportObject($report);
        
        $form = $this->createForm(new FormDir\AccountType(), $account);

        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $data->setReport($reportId);
            $this->get('restClient')->post('report/'.$reportId.'/account', $account);
            
            return $this->redirect($this->generateUrl('accounts_banks', ['reportId'=>$reportId]) );
        
        }

        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
            'subsection' => 'banks',
            'form' => $form->createView()
        ]; 
    }

    /**
     * @Route("/report/{reportId}/accounts/banks/{id}/edit", name="edit_account")
     * @param integer $reportId
     * @param integer $id
     * @param Request $request
     * @Template()
     * @return array
     */
    public function editAction(Request $request, $reportId, $id) 
    {

        $restClient = $this->getRestClient(); /* @var $restClient RestClient */

        $report = $this->getReportIfReportNotSubmitted($reportId);

        if (!in_array($id, $report->getAccounts())) {
            throw new \RuntimeException("Account not found.");
        }
        
        $account = $restClient->get('report/account/' . $id, 'Account');

        $form = $this->createForm(new FormDir\AccountType(), $account);
        $form->handleRequest($request);

        if($form->isValid()){

            $data = $form->getData();
            $data->setReport($reportId);
            $restClient->put('/account/' . $id, $account);

            return $this->redirect($this->generateUrl('accounts_banks', ['reportId'=>$reportId]));
        
        }

        $client = $this->getClient($report->getClient());

        return [
            'report' => $report,
            'client' => $client,
            'subsection' => 'banks',
            'form' => $form->createView()
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
        $report = $this->getReportIfReportNotSubmitted($reportId);
        $restClient = $this->getRestClient(); /* @var $restClient RestClient */

        if(!empty($report) && in_array($id, $report->getAccounts())){
            $restClient->delete("/account/{$id}");
        }

        return $this->redirect($this->generateUrl('accounts_banks', [ 'reportId' => $reportId ]));

    }
    
}

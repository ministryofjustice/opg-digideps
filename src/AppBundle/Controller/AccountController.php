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

class AccountController extends AbstractController
{
    
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
            return $this->redirect($this->generateUrl('accounts_moneyout', ['reportId'=>$reportId]) );
        }

        $client = $this->getClient($report->getClient());

        return [
            'report' => $report,
            'client' => $client,
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
            return $this->redirect($this->generateUrl('accounts_balance', ['reportId'=>$reportId]) );
        }
        
        $client = $this->getClient($report->getClient());
        return [
            'report' => $report,
            'client' => $client,
            'subsection' => "moneyout",
            'jsonEndpoint' => 'transactionsIn',
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
        
        $report = $this->getReport($reportId, [ 'basic', 'balance']);
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
            return $this->redirect($this->generateUrl('assets', [ 'reportId' => $reportId]));
        }
        
        $client = $this->getClient($report->getClient());
        
        return [
            'report' => $report,
            'client' => $client,
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
        $restClient = $this->get('restClient'); /* @var $restClient RestClient */

        $report = $this->getReport($reportId, ['basic','balance']);
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

        if ($form->isValid()) {

            $data = $form->getData();
            $data->setReport($reportId);
            $this->get('restClient')->post('report/' . $reportId . '/account', $account, [
                'deserialise_group' => 'add_edit'
            ]);

            return $this->redirect($this->generateUrl('accounts', ['reportId' => $reportId]));

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
            $restClient->put('/account/' . $id, $account, [
                'deserialise_group' => 'add_edit'
            ]);

            return $this->redirect($this->generateUrl('accounts', ['reportId'=>$reportId]));
        
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
     * @return JsonResponse
     */
    public function moneySaveJson(Request $request, $reportId, $type)
    {
        try {
            $report = $this->getReport($reportId, [$type, 'basic', 'balance']);
            if ($report->getSubmitted()) {
                throw new \RuntimeException("Report already submitted and not editable.");
            }

            $form = $this->createForm(new FormDir\TransactionsType($type), $report, [
                'method' => 'PUT'
            ]);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                $errorsArray = $this->get('formErrorsFormatter')->toArray($form);

                return new JsonResponse(['success' => false, 'errors' => $errorsArray], 500);
            }
            $this->get('restClient')->put('report/' . $report->getId(), $form->getData(), [
                'deserialise_group' => $type,
            ]);
            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }




}

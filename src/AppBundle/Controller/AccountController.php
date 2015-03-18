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
    public function accountsAction($reportId,$action)
    {
        $util = $this->get('util');
        $request = $this->getRequest();
         
        $report = $util->getReport($reportId);
        $client = $util->getClient($report->getClient());
        
        $apiClient = $this->get('apiclient');
        $accounts = $apiClient->getEntities('Account','get_report_accounts', [ 'query' => ['id' => $reportId ]]);
       
        $account = new EntityDir\Account();
        $account->setReportObject($report);
        
        $form = $this->createForm(new FormDir\AccountType(), $account);
        $form->handleRequest($request);
        
        if($request->getMethod() == 'POST'){
            if($form->isValid()){
                $account = $form->getData();
                $account->setReport($reportId);
                
                $apiClient->postC('add_report_account', $account);
                return $this->redirect($this->generateUrl('accounts', [ 'reportId' => $reportId ]));
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
     * @Route("/report/{reportId}/account/{accountId}", name="account", requirements={
     *   "accountId" = "\d+"
     * })
     * @Template()
     */
    public function accountAction($reportId, $accountId, $action = 'list')
    {
        $util = $this->get('util');
        $request = $this->getRequest();
         
        $report = $util->getReport($reportId);
        $client = $util->getClient($report->getClient());
        
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        $account = $this->getMockccount($report); //$apiClient->getEntity('Account', 'find_account_by_id', [ 'query' => ['id' => $accountId ]]);
        
        $form = $this->createForm(new FormDir\AccountTransactionsType(), $account);
        
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $this->debugFormData($form);
            
        }
        
        return [
            'report' => $report,
            'client' => $client,
            'form' => $form->createView(),
            'account' => $account
        ];
    }
    
    private function debugFormData($form)
    {
        echo "<pre>";
        if (!$form->isValid()) {
            echo $form->getErrorsAsString();
            die;
        }
        $account = $form->getData();

        $context = \JMS\Serializer\SerializationContext::create()
                ->setSerializeNull(true)
                ->setGroups('transactions');
        
        $data = $this->get('jms_serializer')->serialize($account, 'json', $context);

        echo "data passed to API: " . print_r(json_decode($data, 1), 1);die;
    }
    
    // until API not reay
    private function getMockccount($report)
    {
        // fake data until API is not ready
        $account = new EntityDir\Account;
        $account->setId(1);
        $account->setReportObject($report);
        $account->setMoneyIn([
            new EntityDir\AccountTransaction('disability_living_allowance_or_personal_independence_payment', 2500),
            new EntityDir\AccountTransaction('attendance_allowance', 450),
            new EntityDir\AccountTransaction('employment_support_allowance_or_incapacity_benefit', 1250),
            new EntityDir\AccountTransaction('severe_disablement_allowance', 1250),
            new EntityDir\AccountTransaction('income_support_or_pension_credit', 1250),
            new EntityDir\AccountTransaction('housing_benefit', 1250),
            new EntityDir\AccountTransaction('state_pension', 120),
            new EntityDir\AccountTransaction('universal_credit', 150),
            new EntityDir\AccountTransaction('other_benefits_eg_winter_fuel_or_cold_weather_payments', 1050),
            new EntityDir\AccountTransaction('occupational_pensions', 120),
            new EntityDir\AccountTransaction('account_interest', 150),
            new EntityDir\AccountTransaction('income_from_investments_property_or_dividends', 850),
            new EntityDir\AccountTransaction('salary_or_wages', 100),
            new EntityDir\AccountTransaction('refunds', 1200),
            new EntityDir\AccountTransaction('bequests_eg_inheritance_gifts_received', 750),
            new EntityDir\AccountTransaction('sale_of_investments_property_or_assets', 650),
            new EntityDir\AccountTransaction('compensation_or_damages_awards', 250),
            new EntityDir\AccountTransaction('transfers_in_from_client_s_other_accounts', 750),
            new EntityDir\AccountTransaction('any_other_money_paid_in_and_not_listed_above', 550),
        ]);
        $account->setMoneyOut([
            new EntityDir\AccountTransaction('care_fees_or_local_authority_charges_for_care', 455),
            new EntityDir\AccountTransaction('accommodation_costs_eg_rent_mortgage_service_charges', 255),
            new EntityDir\AccountTransaction('household_bills_eg_water_gas_electricity_phone_council_tax', 255),
            new EntityDir\AccountTransaction('day_to_day_living_costs_eg_food_toiletries_clothing_sundries', 255),
            new EntityDir\AccountTransaction('debt_payments_eg_loans_cards_care_fee_arrears', 255),
            new EntityDir\AccountTransaction('travel_costs_for_client_eg_bus_train_taxi_fares', 255),
            new EntityDir\AccountTransaction('holidays_or_day_trips', 255),
            new EntityDir\AccountTransaction('tax_payable_to_hmrc', 255),
            new EntityDir\AccountTransaction('insurance_eg_life_home_and_contents', 255),
            new EntityDir\AccountTransaction('office_of_the_public_guardian_fees', 255),
            new EntityDir\AccountTransaction('any_other_money_paid_out_and_not_listed_above', 255),
        ]);
        
        return $account;
    }
}
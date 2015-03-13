<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;


/**
 * @Route("/report")
 */
class ReportController extends RestController
{
    /**
     * @Route("/add")
     * @Method({"POST"})
     */
    public function addAction()
    {
        $reportData = $this->deserializeBodyContent();
       
        $client = $this->findEntityBy('Client', $reportData['client']);
        $courtOrderType = $this->findEntityBy('CourtOrderType', $reportData['court_order_type']);
        
        if(empty($client)){
            throw new \Exception("Client id: ".$reportData['client']." does not exists");
        }
        
        if(empty($courtOrderType)){
            throw new \Exception("Court Order Type id: ".$reportData['court_order_type']." does not exists");
        }
        
        $report = new EntityDir\Report();
        $report->setStartDate(new \DateTime($reportData['start_date']));
        $report->setEndDate(new \DateTime($reportData['end_date']));
        $report->setCourtOrderType($courtOrderType);
        $report->setClient($client);
        
        $this->getEntityManager()->persist($report);
        $this->getEntityManager()->flush();
        
        return [ 'report' => $report->getId()] ;
    }
    
     
   /**
     * @Route("/find-by-id/{id}")
     * @Method({"GET"})
     */
    public function get($id)
    {
        $ret = $this->findEntityBy('Report', $id, 'Report not found');

        return $ret;
    }
        
    /**
     * @Route("/add-contact")
     * @Method({"POST"})
     */
    public function addContactAction()
    {
        $contactData = $this->deserializeBodyContent();
        
        $report = $this->findEntityBy('Report', $contactData['report']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$contactData['report']." does not exists");
        }
        
        $contact = new EntityDir\Contact();
        $contact->setReport($report);
        $contact->setContactName($contactData['contact_name']);
        $contact->setAddress($contactData['address']);
        $contact->setAddress2($contactData['address2']);
        $contact->setCounty($contactData['county']);
        $contact->setPostcode($contactData['postcode']);
        $contact->setCountry($contactData['country']);
        $contact->setExplanation($contactData['explanation']);
        $contact->setRelationship($contactData['relationship']);
        $contact->setLastedit(new \DateTime());
        $this->getEntityManager()->persist($contact);
        $this->getEntityManager()->flush();
        
        return [ 'id' => $contact->getId() ];
    }
    
    /**
     * @Route("/get-contacts/{id}")
     * @Method({"GET"})
     */
    public function getContactsAction($id)
    {
        $report = $this->findEntityBy('Report', $id);
        
        $contacts = $this->getRepository('Contact')->findByReport($report);
        
        if(count($contacts) == 0){
            //throw new \Exception("No contacts found for report id: $id");
            return [];
        }
        return $contacts;
    }
    
    /**
     * @Route("/get-accounts/{id}")
     * @Method({"GET"})
     */
    public function getAccountsAction($id)
    {
        $report = $this->findEntityBy('Report', $id);
        
        $accounts = $this->getRepository('Account')->findByReport($report);
       
        if(count($accounts) == 0){
            return [];
        }
        return $accounts;
    }
    
    /**
     * @Route("/add-account")
     * @Method({"POST"})
     */
    public function addAccountAction()
    {
        $accountData = $this->deserializeBodyContent();
   
        $report = $this->findEntityBy('Report', $accountData['report']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$accountData['report']." does not exists");
        }
        
        $account = new EntityDir\Account();
        $account->setBank($accountData['bank']);
        $account->setSortCode($accountData['sort_code']);
        $account->setAccountNumber($accountData['account_number']);
        $account->setOpeningDate(new \DateTime($accountData['opening_date']));
        $account->setOpeningBalance($accountData['opening_balance']);
        $account->setReport($report);
        $account->setLastEdit(new \DateTime());
        
        $benefits = $this->getRepository('Benefit')->findAll();
        
        if(count($benefits) > 0){
            foreach($benefits as $benefit){
                $account->addBenefit($benefit);
            }
        }
        
        $incomes = $this->getRepository('Income')->findAll();
        
        if(count($incomes) > 0){
            foreach($incomes as $income){
                $account->addIncome($income);
            }
        }
        
        $expenditures = $this->getRepository('Expenditure')->findAll();
        
        if(count($expenditures) > 0){
            foreach($expenditures as $expenditure){
                $account->addExpenditure($expenditure);
            }
        }
        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();
        
        return [ 'id' => $account->getId() ];
    }
    
     /**
     * @Route("/{id}")
     * @Method({"PUT"})
     */
    public function update($id)
    {
        $report = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $report EntityDir\Report */

        $data = $this->deserializeBodyContent();
        
        $cot = $this->findEntityBy('CourtOrderType', $data['cotId']);
        $report->setCourtOrderType($cot);
        
        $this->getEntityManager()->flush($report);
        
        return ['id'=>$report->getId()];
    }
}
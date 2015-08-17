<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;

class ReportController extends RestController
{
    /**
     * @Route("/report/upsert")
     * @Method({"POST"})
     */
    public function upsertAction()
    {
        $reportData = $this->deserializeBodyContent();
       
        if (!empty($reportData['id'])) { 
            // get existing report
            $report = $this->findEntityBy('Report', $reportData['id']);
        } else {
            // new report
             $client = $this->findEntityBy('Client', $reportData['client']);
            if(empty($client)){
                throw new \Exception("Client id: ".$reportData['client']." does not exists");
            }
            $report = new EntityDir\Report();
            $report->setClient($client);
        }
        
        // add court order type
        $courtOrderType = $this->findEntityBy('CourtOrderType', $reportData['court_order_type']);
        if(empty($courtOrderType)){
            throw new \Exception("Court Order Type id: ".$reportData['court_order_type']." does not exists");
        }
        $report->setCourtOrderType($courtOrderType);
        
        // add other stuff
        $report->setStartDate(new \DateTime($reportData['start_date']));
        $report->setEndDate(new \DateTime($reportData['end_date']));
        $report->setWhenWasCarePlanLastReviewed(null);
        $report->setReportSeen(true);
        
        // persist
        $this->getEntityManager()->persist($report);
        $this->getEntityManager()->flush();
        
        return [ 'report' => $report->getId()] ;
    }
    
    /**
     * @Route("/report/clone")
     * @Method({"POST"})
     */
    public function cloneAction()
    {
        $reportData = $this->deserializeBodyContent();
        
        $report = $this->findEntityBy('Report', $reportData['id']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$reportData['id']." does not exists");
        }
        //lets clone the report
        $newReport = new EntityDir\Report();
        $newReport->setClient($report->getClient());
        $newReport->setCourtOrderType($report->getCourtOrderType());
        $newReport->setStartDate($report->getEndDate()->modify('+1 day'));
        $newReport->setEndDate($report->getEndDate()->modify('+12 months -1 day'));
        $newReport->setReportSeen(false);
        $newReport->setNoAssetToAdd($report->getNoAssetToAdd());
        $newReport->setWhenWasCarePlanLastReviewed(null);
        
        //lets clone the assets
        $assets = $report->getAssets();
        
        foreach($assets as $asset){
            $newAsset = new EntityDir\Asset();
            $newAsset->setDescription($asset->getDescription());
            $newAsset->setTitle($asset->getTitle());
            $newAsset->setValuationDate($asset->getValuationDate());
            $newAsset->setValue($asset->getValue());
            $newAsset->setReport($newReport);
            
            $this->getEntityManager()->persist($newAsset);
        }
        
        //lets clone accounts
        $accounts = $report->getAccounts();
        
        foreach($accounts as $account){
            $newAccount = new EntityDir\Account();
            $newAccount->setBank($account->getBank());
            $newAccount->setSortCode($account->getSortCode());
            $newAccount->setAccountNumber($account->getAccountNumber());
            $newAccount->setOpeningBalance($account->getClosingBalance());
            $newAccount->setOpeningDate($account->getClosingDate());
            $newAccount->setCreatedAt(new \DateTime());
            $newAccount->setReport($newReport);

            $this->getRepository('Account')->addEmptyTransactionsToAccount($newAccount);
            
            $this->getEntityManager()->persist($newAccount);
        }
        // persist
        $this->getEntityManager()->persist($newReport);
        $this->getEntityManager()->flush();
        
        return [ 'report' => $newReport->getId()] ;
    }
    
     
   /**
     * @Route("/report/find-by-id/{id}/{userId}")
     * @Method({"GET"})
     * 
     * @param integer $id
     * @param integer $userId to check the record is accessible by this user
     */
    public function get($id, $userId)
    {   
        $request = $this->getRequest();
        
        $serialiseGroups = $request->query->has('groups')? $request->query->get('groups') : [ 'basic'];
        
        $this->setJmsSerialiserGroup($serialiseGroups);
        
        $report = $this->getRepository('Report')->find($id); /* @var $report EntityDir\Report */
        
        // throw exception if the report does not exist or it's not accessible from the given user
        if (empty($report) || !in_array($userId, $report->getClient()->getUserIds())) {
            throw new \Exception("Report not found");
        }
        
        return $report;
    }
        
    /**
     * @Route("/report/upsert-contact")
     * @Method({"POST", "PUT"})
     **/
    public function upsertContactAction()
    {
        $contactData = $this->deserializeBodyContent();
        $request = $this->getRequest();
       
        $report = $this->findEntityBy('Report',$contactData['report']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$contactData['report']." does not exists");
        }
        
        if($request->getMethod() == "POST"){
            $contact = new EntityDir\Contact();
            $contact->setReport($report);
        }else{
            $contact = $this->findEntityBy('Contact', $contactData['id']);
            
            if(empty($contact)){
                throw new \Exception("Contact with id: ".$contactData['id']);
            }
        }
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
     * @Route("/report/get-contacts/{id}")
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
     * @Route("/report/get-contact/{id}")
     * @Method({"GET"})
     */
    public function getContactAction($id)
    {
        $request = $this->getRequest();
        
        $serialisedGroups = ['basic'];
        
        if($request->query->has('groups')){
            $serialisedGroups = $request->query->get('groups');
        }
        $this->setJmsSerialiserGroup($serialisedGroups);
        
        $contact = $this->findEntityBy('Contact', $id);
        
        if(empty($contact)){
            throw new \Exception("Contact with id: $id does not exist");
        }
        return $contact;
    }
    
    /**
     * 
     * @Route("/report/get-assets/{id}")
     * @Method({"GET"})
     */
    public function getAssetsAction($id)
    {
        $report = $this->findEntityBy('Report', $id);
        $assets = $this->getRepository('Asset')->findByReport($report);
        
        if(count($assets) == 0){
            return [];
        }
        return $assets;
    }
    
    /**
     * @Route("/report/get-asset/{id}")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getAssetAction($id)
    { 
        $asset = $this->findEntityBy('Asset', $id);
        
        if(empty($asset)){
            throw new \Exception("Asset with id: $id does not exist");
        }
        return $asset;
    }
    
    /**
     * @Route("/report/upsert-asset")
     * @Method({"POST", "PUT"})
     */
    public function upsertAssetAction()
    {
        $request = $this->getRequest();
        
        $assetData = $this->deserializeBodyContent();
        
        $report = $this->findEntityBy('Report', $assetData['report']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$assetData['report']." does not exists");
        }
        
        if($request->getMethod() == 'POST'){
            $asset = new EntityDir\Asset();
            $asset->setReport($report);
        }else{
            $asset = $this->findEntityBy('Asset', $assetData['id']);
            
            if(empty($asset)){
                throw new \Exception("Asset with id:".$assetData['id'].' was not found');
            }
        }
        
        $asset->setDescription($assetData['description']);
        $asset->setValue($assetData['value']);
        $asset->setTitle($assetData['title']);
        
        if(!empty($assetData['valuation_date'])){
            $valuationDate = new \DateTime($assetData['valuation_date']);
        }else{
            $valuationDate = null;
        }
        
        $asset->setValuationDate($valuationDate);
        $asset->setLastedit(new \DateTime());
        $this->getEntityManager()->persist($asset);
        $this->getEntityManager()->flush();
        
        return [ 'id' => $asset->getId() ];
    }
    
    /**
     * @Route("/report/{id}")
     * @Method({"PUT"})
     */
    public function update($id)
    {
        $report = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $report EntityDir\Report */

        $data = $this->deserializeBodyContent();
        
        if (array_key_exists('cot_id', $data)) {
            $cot = $this->findEntityBy('CourtOrderType', $data['cot_id']);
            $report->setCourtOrderType($cot);
        }
        
        if (array_key_exists('start_date', $data)) {
            $report->setStartDate(new \DateTime($data['start_date']));
        }
        
        if (array_key_exists('end_date', $data)) {
            $report->setEndDate(new \DateTime($data['end_date']));
        }
        
        if (array_key_exists('submitted', $data)) {
            $report->setSubmitted((boolean)$data['submitted']);
        }
        
        if (array_key_exists('reviewed', $data)) {
            $report->setReviewed((boolean)$data['reviewed']);
        }
        
        if (array_key_exists('report_seen', $data)) {
            $report->setReportSeen((boolean)$data['report_seen']);
        }
        
        if (array_key_exists('submit_date', $data)) {
            $report->setSubmitDate(new \DateTime($data['submit_date']));
        }
        
        if (array_key_exists('reason_for_no_contacts', $data)) {
            $report->setReasonForNoContacts($data['reason_for_no_contacts']);
        }
        
        if (array_key_exists('no_asset_to_add', $data)) {
            $report->setNoAssetToAdd($data['no_asset_to_add']);
        }
        
        if (array_key_exists('reason_for_no_decisions', $data)) {
            $report->setReasonForNoDecisions($data['reason_for_no_decisions']);
        }
        
        if (array_key_exists('further_information', $data)) {
            $report->setFurtherInformation($data['further_information']);
        }

        $report = $this->updateSafeguardingInfo($data,$report);
        
        $this->getEntityManager()->flush($report);
        
        return ['id'=>$report->getId()];
    }
    
    /**
     * 
     * @Route("report/delete-contact/{id}")
     * @Method({"DELETE"})
     */
    public function deleteContactAction($id)
    {
        $contact = $this->findEntityBy('Contact', $id, 'Contact not found');
        
        $this->getEntityManager()->remove($contact);
        $this->getEntityManager()->flush();
        
        return [ ];
    }
    
    /**
     * @Route("report/delete-asset/{id}")
     * @Method({"DELETE"})
     * 
     * @param type $id
     */
    public function deleteAssetAction($id)
    { 
        $asset = $this->findEntityBy('Asset', $id, 'Asset not found');
        
        $this->getEntityManager()->remove($asset);
        $this->getEntityManager()->flush();
        
        return [ ];
    }
    
    /**
     * @Route("/report/{id}/contacts")
     * @Method({"DELETE"})
     */
    public function contactsDelete($id)
    {
        $report = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $report EntityDir\Report */

        foreach ($report->getContacts() as $contact) {
            $this->getEntityManager()->remove($contact);
        }
        $report->setReasonForNoContacts(null);
        
        $this->getEntityManager()->flush();
        
        return ['id'=>$report->getId()];
    }

    /**
     * @param  array $data   
     * @param  AppBundle\Entity\Report $report
     * @return AppBundle\Entity\Report $report  
     */
    private function updateSafeguardingInfo($data,\AppBundle\Entity\Report $report)
    {
        if(array_key_exists('do_you_live_with_client', $data)) {
            $report->setDoYouLiveWithClient($data['do_you_live_with_client']);
        }

        if(array_key_exists('how_often_do_you_visit', $data)) {
            $report->setHowOftenDoYouVisit($data['how_often_do_you_visit']);
        }

        if(array_key_exists('how_often_do_you_phone_or_video_call', $data)) {
            $report->setHowOftenDoYouPhoneOrVideoCall($data['how_often_do_you_phone_or_video_call']);
        }

        if(array_key_exists('how_often_do_you_write_email_or_letter', $data)) {
            $report->setHowOftenDoYouWriteEmailOrLetter($data['how_often_do_you_write_email_or_letter']);
        }

        if(array_key_exists('how_often_does_client_see_other_people', $data)) {
            $report->setHowOftenDoesClientSeeOtherPeople($data['how_often_does_client_see_other_people']);
        }

        if(array_key_exists('anything_else_to_tell', $data)) {
            $report->setAnythingElseToTell($data['anything_else_to_tell']);
        }

        if(array_key_exists('does_client_receive_paid_care', $data)) {
            $report->setDoesClientReceivePaidCare($data['does_client_receive_paid_care']);
        }

        if(array_key_exists('how_is_care_funded', $data)) {
            $report->setHowIsCareFunded($data['how_is_care_funded']);
        }

        if(array_key_exists('who_is_doing_the_caring', $data)) {
            $report->setWhoIsDoingTheCaring($data['who_is_doing_the_caring']);
        }

        if(array_key_exists('does_client_have_a_care_plan', $data)) {
            $report->setDoesClientHaveACarePlan($data['does_client_have_a_care_plan']);
        }

        if(array_key_exists('when_was_care_plan_last_reviewed', $data)) {

            if(!empty($data['when_was_care_plan_last_reviewed'])){
                $report->setWhenWasCarePlanLastReviewed(new \DateTime($data['when_was_care_plan_last_reviewed']));
            }else{
                $report->setWhenWasCarePlanLastReviewed(null);
            }
        }

        return $report;
    }
    
}
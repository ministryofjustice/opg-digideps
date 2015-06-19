<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity as EntityDir;

class ReportController extends RestController
{
    /**
     * @Route("/report/add")
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
     * @Route("/report/find-by-id/{id}")
     * @Method({"GET"})
     */
    public function get($id)
    {   
        $request = $this->getRequest();
        
        $serialiseGroups = $request->query->has('groups')? $request->query->get('groups') : [ 'basic'];
        
        $this->setJmsSerialiserGroup($serialiseGroups);
        
        $ret = $this->getRepository('Report')->find($id);
        
        if(empty($ret)){
            throw new \Exception("Report not found");
        }
        
        return $ret;
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
        
        if (array_key_exists('no_asset_to_add', $data)) {
            $report->setNoAssetToAdd($data['no_asset_to_add']);
        }
        
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
    
}
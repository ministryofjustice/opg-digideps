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
     * @Route("/report/find-by-id/{userId}/{id}/{serialiseGroup}", defaults={ "serialiseGroup" = "basic" })
     * @Method({"GET"})
     */
    public function get($userId,$id,$serialiseGroup = null)
    {   
        $this->setJmsSerialiserGroup($serialiseGroup);
        $ret = $this->getRepository('Report')->findByIdAndUser($id,$userId);
   
        if(empty($ret)){
            throw new \Exception("Report not found");
        }
        
        return $ret;
    }
        
    /**
     * @Route("/report/add-contact")
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
     * @Route("/report/add-asset")
     * @Method({"POST"})
     */
    public function addAssetAction()
    {
        $reportData = $this->deserializeBodyContent();
        
        $report = $this->findEntityBy('Report', $reportData['report']);
        
        if(empty($report)){
            throw new \Exception("Report id: ".$reportData['report']." does not exists");
        }
        
        $asset = new EntityDir\Asset();
        $asset->setReport($report);
        $asset->setDescription($reportData['description']);
        $asset->setValue($reportData['value']);
        $asset->setTitle($reportData['title']);
        
        if(!empty($reportData['valuation_date'])){
            $valuationDate = new \DateTime($reportData['valuation_date']);
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
        
        if (array_key_exists('cotId', $data)) {
            $cot = $this->findEntityBy('CourtOrderType', $data['cotId']);
            $report->setCourtOrderType($cot);
        }
        
        if (array_key_exists('endDate', $data)) {
            $report->setEndDate(new \DateTime($data['endDate']));
        }
        
        $this->getEntityManager()->flush($report);
        
        return ['id'=>$report->getId()];
    }
}
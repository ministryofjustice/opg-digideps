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
}
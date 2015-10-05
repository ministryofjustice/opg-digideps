<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;
use AppBundle\Exception as AppExceptions;


class ReportController extends RestController
{
    /**
     * @Route("/report/upsert")
     * @Method({"POST"})
     */
    public function upsertAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $reportData = $this->deserializeBodyContent($request);
       
        if (!empty($reportData['id'])) { 
            // get existing report
            $report = $this->findEntityBy('Report', $reportData['id']);
            $this->denyAccessIfReportDoesNotBelongToUser($report);
        } else {
            // new report
            $client = $this->findEntityBy('Client', $reportData['client']);
            $this->denyAccessIfClientDoesNotBelongToUser($client);
            
            $report = new EntityDir\Report();
            $report->setClient($client);
        }
        
        // add court order type
        $courtOrderType = $this->findEntityBy('CourtOrderType', $reportData['court_order_type']);
        $report->setCourtOrderType($courtOrderType);
        
        // add other stuff
        $report->setStartDate(new \DateTime($reportData['start_date']));
        $report->setEndDate(new \DateTime($reportData['end_date']));
        $report->setReportSeen(true);
        
        // persist
        $this->getEntityManager()->persist($report);
        $this->getEntityManager()->flush();
        
        return [ 'report' => $report->getId()] ;
    }
    
    /**
     * deprecated. only kept as a useful test for written for it.
     * move the test to the repository method, then delete this
     * @Route("/report/clone")
     * @Method({"POST"})
     */
    public function cloneAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $reportData = $this->deserializeBodyContent($request);
        
        $report = $this->findEntityBy('Report', $reportData['id']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        $newReport = $this->getRepository('Report')->createNextYearReport($report);
        
        return [ 'report' => $newReport->getId()] ;
    }
    
     
   /**
     * @Route("/report/find-by-id/{id}")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getById(Request $request, $id)
    {   
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array)$request->query->get('groups'));
        }
        
        $report = $this->findEntityBy('Report', $id); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        return $report;
    }
        
    /**
     * @Route("/report/upsert-contact")
     * @Method({"POST", "PUT"})
     **/
    public function upsertContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $contactData = $this->deserializeBodyContent($request);
       
        $report = $this->findEntityBy('Report',$contactData['report']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        if($request->getMethod() == "POST"){
            $contact = new EntityDir\Contact();
            $contact->setReport($report);
        }else{
            $contact = $this->findEntityBy('Contact', $contactData['id']);
            
            if(empty($contact)){
                throw new AppExceptions\NotFound("Contact with id: ".$contactData['id'], 404);
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
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        $contacts = $this->getRepository('Contact')->findByReport($report);
        
        if(count($contacts) == 0){
            //throw new AppExceptions\NotFound("No contacts found for report id: $id", 404);
            return [];
        }
        return $contacts;
    }
    
    /**
     * @Route("/report/get-contact/{id}")
     * @Method({"GET"})
     */
    public function getContactAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $serialisedGroups = $request->query->has('groups') ? (array)$request->query->get('groups') : ['basic'];
        $this->setJmsSerialiserGroups($serialisedGroups);
        
        $contact = $this->findEntityBy('Contact', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());
        
        return $contact;
    }
    
    /**
     * 
     * @Route("/report/get-assets/{id}")
     * @Method({"GET"})
     */
    public function getAssetsAction($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $report = $this->findEntityBy('Report', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
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
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $asset = $this->findEntityBy('Asset', $id);
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());
        
        return $asset;
    }
    
    /**
     * @Route("/report/upsert-asset")
     * @Method({"POST", "PUT"})
     */
    public function upsertAssetAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $assetData = $this->deserializeBodyContent($request);
        
        $report = $this->findEntityBy('Report', $assetData['report']);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        if($request->getMethod() == 'POST'){
            $asset = new EntityDir\Asset();
            $asset->setReport($report);
        }else{
            $asset = $this->findEntityBy('Asset', $assetData['id']);
            
            if(empty($asset)){
                throw new AppExceptions\NotFound("Asset with id:".$assetData['id'].' was not found', 404);
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
     * @Route("/report/{id}/user/{userId}/submit")
     * @Method({"PUT"})
     */
    public function submit(Request $request, $id)
    { 
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $currentReport = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $currentReport EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($currentReport);
        $user = $this->getUser(); 
        $client = $currentReport->getClient();
        
        $data = $this->deserializeBodyContent($request);
        
        if (empty($data['submit_date'])) {
            throw new \InvalidArgumentException("Missing submit_date");
        }
        
        $currentReport->setSubmitted(true);
        $currentReport->setSubmitDate(new \DateTime($data['submit_date']));
            
            
        // send report if submitted
        $reportContent = $this->forward('AppBundle:Report:formatted', ['reportId'=>$currentReport->getId()])->getContent();
        
        $reportEmail = $this->getMailFactory()->createReportEmail($user, $client, $reportContent);
        $this->getMailSender()->send($reportEmail,[ 'html'], 'secure-smtp');

        //lets create subsequent year's report
        $nextYearReport = $this->getRepository('Report')->createNextYearReport($currentReport);

        //send confirmation email
        $reportConfirmEmail = $this->getMailFactory()->createReportSubmissionConfirmationEmail($user, $currentReport, $nextYearReport);
        $this->getMailSender()->send($reportConfirmEmail, [ 'text', 'html']);

        //response to pass back
        return ['newReportId' =>  $nextYearReport->getId()];
    }
    
    /**
     * @Route("/report/{reportId}/formatted")
     */
    public function formattedAction($reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $report = $this->getRepository('Report')->find($reportId); /*@var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        
        return $this->render('AppBundle:Report:formatted.html.twig', [
            'report' => $report,
            'client' =>  $report->getClient(),
            'assets' => $report->getAssets(),
            'groupAssets' => $report->getAssetsGroupedByTitle(),
            'contacts' => $report->getContacts(),
            'decisions' => $report->getDecisions(),
            'isEmailAttachment' => true,
            'deputy' => $report->getClient()->getUsers()->first(),
        ]);
    }
    
    
    /**
     * @Route("/report/{id}")
     * @Method({"PUT"})
     */
    public function update(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $report = $this->findEntityBy('Report', $id, 'Report not found'); /* @var $report EntityDir\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $data = $this->deserializeBodyContent($request);
        
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
        
        if (array_key_exists('reviewed', $data)) {
            $report->setReviewed((boolean)$data['reviewed']);
        }
        
        if (array_key_exists('report_seen', $data)) {
            $report->setReportSeen((boolean)$data['report_seen']);
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
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $contact = $this->findEntityBy('Contact', $id, 'Contact not found');
        $this->denyAccessIfReportDoesNotBelongToUser($contact->getReport());
        
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
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        
        $asset = $this->findEntityBy('Asset', $id, 'Asset not found');
        $this->denyAccessIfReportDoesNotBelongToUser($asset->getReport());
        
        $this->getEntityManager()->remove($asset);
        $this->getEntityManager()->flush();
        
        return [ ];
    }
    
}
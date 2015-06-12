<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Entity\Decision;

/**
 * @Route("/decision")
 */
class DecisionController extends RestController
{
    /**
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     */
    public function upsertAction()
    {
        $data = $this->deserializeBodyContent();
        $request = $this->getRequest();

        if($request->getMethod() == "PUT"){
            $decision = $this->findEntityBy('Decision', $data['id']);
        }else{
            $report = $this->findEntityBy('Report', $data['report_id'], 'Report not found');
            $decision = new Decision();
            $decision->setReport($report);
        }

        $this->hydrateEntityWithArrayData($decision, $data, [
            'description' => 'setDescription',
            'client_involved_boolean' => 'setClientInvolvedBoolean',
            'client_involved_details' => 'setClientInvolvedDetails',
        ]);

        $this->getEntityManager()->persist($decision);
        $this->getEntityManager()->flush();

        return ['id' => $decision->getId() ];
    }


    /**
     * @Route("/find-by-report-id/{reportId}")
     * @Method({"GET"})
     *
     * @param integer $reportId
     */
    public function findByReportIdAction($reportId)
    {
        if(!$this->container->getParameter('anonymous')){
            $currentUser = $this->getRequest()->getSession()->get('currentUser');
            $report = $this->getRepository('Report')->findByIdAndUser($reportId,$currentUser->getId());
        }else{
            $report = $this->findEntityBy('Report', $reportId); 
        }
        
        return $this->getRepository('Decision')->findBy(['report'=>$report]);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     * @param integer $id
     */
    public function get($id)
    {
        $request = $this->getRequest();
        $serialiseGroups = $request->query->has('groups')? $request->query->get('groups') : [ 'basic'];
        $this->setJmsSerialiserGroup($serialiseGroups);
        
        $decision = $this->findEntityBy('Decision', $id, "Decision with id:".$id." not found");
        
        return $decision;
    }
    
    /**
     * @Route("/{id}")
     * @Method({"DELETE"})
     */
    public function deleteAction($id)
    {
        $decision = $this->findEntityBy('Decision', $id, 'Decision not found');
        
         $this->getEntityManager()->remove($decision);
         $this->getEntityManager()->flush();
         
         return [ ];
    }
}

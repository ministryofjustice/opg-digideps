<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Decision;
use AppBundle\Exception as AppExceptions;


/**
 * @Route("/decision")
 */
class DecisionController extends RestController
{
    /**
     * @Route("/upsert")
     * @Method({"POST", "PUT"})
     */
    public function upsertAction(Request $request)
    {
        $data = $this->deserializeBodyContent($request);

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
        $report = $this->findEntityBy('Report', $reportId); 
       
        return $this->getRepository('Decision')->findBy(['report'=>$report]);
    }
    
    /**
     * @Route("/{id}")
     * @Method({"GET"})
     * @param integer $id
     */
    public function getOneById(Request $request, $id)
    {
        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array)$request->query->get('groups'));
        }
        
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

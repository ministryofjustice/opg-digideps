<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Decision;
use AppBundle\Exception\NotFound;

/**
 * @Route("/decision")
 */
class DecisionController extends RestController
{
    /**
     * @Route("/add")
     * @Method({"POST"})
     */
    public function addAction()
    {
        $data = $this->deserializeBodyContent();
      
        // read user
        $report = $this->findEntityBy('Report', $data['report_id'], 'Report not found');

        $decision = new Decision();
        $decision->setReport($report);
        
        $this->hydrateEntityWithArrayData($decision, $data, [
            'title' => 'setTitle', 
            'description' => 'setDescription', 
            'client_involved_boolean' => 'setClientInvolvedBoolean', 
            'client_involved_details' => 'setClientInvolvedDetails', 
        ]);
        if (array_key_exists('decision_date', $data)) {
            $decision->setDecisionDate(new \DateTime($data['decision_date']));
        }
        
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
}

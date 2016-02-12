<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class ConcernController extends RestController
{

    /**
     * @Route("/report/{reportId}/concern")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
            
        $concern = $report->getConcern();
        if (!$concern) {
            $concern =  new EntityDir\Concern($report);
            $this->getEntityManager()->persist($concern);
        } 

        $data = $this->deserializeBodyContent($request);
        $this->updateConcern($data, $concern);

        $this->getEntityManager()->flush($concern);

        return ['id' => $concern->getId()];
    }


    /**
     * @Route("/report/{reportId}/concern")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $concern = $this->findEntityBy('Concern', $id, "Concern with id:" . $id . " not found");
        $this->denyAccessIfReportDoesNotBelongToUser($concern->getReport());

        return $concern;
    }

    /**
     * @param array $data
     * @param EntityDir\Concern $concern
     * 
     * @return \AppBundle\Entity\Report $report
     */
    private function updateConcern(array $data, EntityDir\Concern $concern)
    {
        if (array_key_exists('do_you_expect_financial_decisions', $data)) {
            $concern->setDoYouExpectFinancialDecisions($data['do_you_expect_financial_decisions']);
        }
        
        if (array_key_exists('do_you_expect_financial_decisions_details', $data)) {
            $concern->setDoYouExpectFinancialDecisionsDetails($data['do_you_expect_financial_decisions_details']);
        }

        if (array_key_exists('do_you_have_concerns', $data)) {
            $concern->setDoYouHaveConcerns($data['do_you_have_concerns']);
        }
        
        if (array_key_exists('do_you_have_concerns_details', $data)) {
            $concern->setDoYouHaveConcernsDetails($data['do_you_have_concerns_details']);
        }

        return $concern;
    }

}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Decision;

/**
 * @Route("/report")
 */
class DecisionController extends RestController
{
    /**
     * @Route("/{reportId}/decision")
     * @Method({"GET"})
     *
     * @param integer $reportId
     */
    public function getDecisions($reportId)
    {
        $report = $this->findEntityBy('Report', $reportId);

        return $this->getRepository('Decision')->findBy(['report' => $report]);
    }
    
    /**
     * @Route("/decision")
     * @Method({"POST", "PUT"})
     */
    public function upsertDecision(Request $request)
    {
        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == "PUT") {
            $decision = $this->findEntityBy('Decision', $data['id']);
        } else {
            $report = $this->findEntityBy('Report', $data['report_id'], 'Report not found');
            $decision = new Decision();
            $decision->setReport($report);
        }

        $this->hydrateEntityWithArrayData($decision, $data, [
            'description' => 'setDescription',
            'client_involved_boolean' => 'setClientInvolvedBoolean',
            'client_involved_details' => 'setClientInvolvedDetails',
        ]);

        $this->persistAndFlush($decision);

        return ['id' => $decision->getId()];
    }


    /**
     * @Route("/decision/{id}")
     * @Method({"GET"})
     * 
     * @param integer $id
     */
    public function getOneById(Request $request, $id)
    {
        if ($request->query->has('groups')) {
            $this->setJmsSerialiserGroups((array)$request->query->get('groups'));
        }

        $decision = $this->findEntityBy('Decision', $id, "Decision with id:" . $id . " not found");

        return $decision;
    }


    /**
     * @Route("/decision/{id}")
     * @Method({"DELETE"})
     */
    public function deleteDecision($id)
    {
        $decision = $this->findEntityBy('Decision', $id, 'Decision not found');

        $this->persistAndFlush($decision);

        return [];
    }

}
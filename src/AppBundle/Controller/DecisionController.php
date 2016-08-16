<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

/**
 * @Route("/report")
 */
class DecisionController extends RestController
{
    /**
     * @Route("/decision")
     * @Method({"POST", "PUT"})
     */
    public function upsertDecision(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'PUT') {
            $this->validateArray($data, [
                'id' => 'mustExist',
            ]);
            $decision = $this->findEntityBy('Decision', $data['id'], 'Decision with not found');
            $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());
        } else {
            $this->validateArray($data, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy('Report', $data['report_id'], 'Report not found');
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $decision = new EntityDir\Decision();
            $decision->setReport($report);
            $report->setReasonForNoDecisions(null);
            $this->persistAndFlush($report);
        }

        $this->validateArray($data, [
            'description' => 'mustExist',
            'client_involved_boolean' => 'mustExist',
            'client_involved_details' => 'mustExist',
        ]);

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
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['decision'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $decision = $this->findEntityBy('Decision', $id, 'Decision with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        return $decision;
    }

    /**
     * @Route("/decision/{id}")
     * @Method({"DELETE"})
     */
    public function deleteDecision($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $decision = $this->findEntityBy('Decision', $id, 'Decision with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        $this->getEntityManager()->remove($decision);
        $this->getEntityManager()->flush($decision);

        return [];
    }
}

<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'PUT') {
            $this->validateArray($data, [
                'id' => 'mustExist',
            ]);
            $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $data['id'], 'Decision with not found');
            $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());
        } else {
            $this->validateArray($data, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id'], 'Report not found');
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $decision = new EntityDir\Report\Decision();
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
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['decision'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $id, 'Decision with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        return $decision;
    }

    /**
     * @Route("/decision/{id}")
     * @Method({"DELETE"})
     */
    public function deleteDecision($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_DEPUTY);

        $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $id, 'Decision with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        $this->getEntityManager()->remove($decision);
        $this->getEntityManager()->flush($decision);

        return [];
    }
}

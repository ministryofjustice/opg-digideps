<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class MentalCapacityController extends RestController
{
    /**
     * @Route("/report/{reportId}/mental-capacity")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);
        $report = $this->findEntityBy('Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $mc = $report->getMentalCapacity();
        if (!$mc) {
            $mc = new EntityDir\MentalCapacity($report);
            $this->getEntityManager()->persist($mc);
        }

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $mc);

        $this->getEntityManager()->flush($mc);

        return ['id' => $mc->getId()];
    }

    /**
     * @Route("/report/{reportId}/mental-capacity")
     * @Method({"GET"})
     * 
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $mc = $this->findEntityBy('MentalCapacity', $id, 'MentalCapacity with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($mc->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['mental-capacity'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $mc;
    }

    /**
     * @param array                    $data
     * @param EntityDir\MentalCapacity $mc
     * 
     * @return \AppBundle\Entity\Report $report
     */
    private function updateEntity(array $data, EntityDir\MentalCapacity $mc)
    {
        if (array_key_exists('has_capacity_changed', $data)) {
            $mc->setHasCapacityChanged($data['has_capacity_changed']);
        }

        if (array_key_exists('has_capacity_changed_details', $data)) {
            $mc->setHasCapacityChangedDetails($data['has_capacity_changed_details']);
        }

        $mc->cleanUpUnusedData();

        return $mc;
    }
}

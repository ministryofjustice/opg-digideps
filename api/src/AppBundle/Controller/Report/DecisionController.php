<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/report")
 */
class DecisionController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_DECISIONS];
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/decision", methods={"POST", "PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function upsertDecision(Request $request)
    {
        $data = $this->deserializeBodyContent($request);

        if ($request->getMethod() == 'PUT') {
            $this->validateArray($data, [
                'id' => 'mustExist',
            ]);
            $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $data['id'], 'Decision with not found');
            $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());
            $report = $decision->getReport();
        } else {
            $this->validateArray($data, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id'], 'Report not found');
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $decision = new EntityDir\Report\Decision();
            $decision->setReport($report);
            $report->setReasonForNoDecisions(null);

            $this->em->persist($report);
            $this->em->flush();
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

        $this->em->persist($decision);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $decision->getId()];
    }

    /**
     * @Route("/decision/{id}", methods={"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $serialisedGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['decision'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $id, 'Decision with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        return $decision;
    }

    /**
     * @Route("/decision/{id}", methods={"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteDecision($id)
    {
        $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $id, 'Decision with id:' . $id . ' not found');
        $report = $decision->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        $this->em->remove($decision);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }
}

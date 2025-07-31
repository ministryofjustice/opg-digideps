<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/report')]
class DecisionController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_DECISIONS];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RestFormatter $formatter,
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/decision', methods: ['POST', 'PUT'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function upsertDecision(Request $request): array
    {
        $data = $this->formatter->deserializeBodyContent($request);

        if ('PUT' == $request->getMethod()) {
            $this->formatter->validateArray($data, [
                'id' => 'mustExist',
            ]);
            $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $data['id'], 'Decision with not found');
            $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());
            $report = $decision->getReport();
        } else {
            $this->formatter->validateArray($data, [
                'report_id' => 'mustExist',
            ]);
            $report = $this->findEntityBy(EntityDir\Report\Report::class, $data['report_id'], 'Report not found');
            $this->denyAccessIfReportDoesNotBelongToUser($report);
            $decision = new EntityDir\Report\Decision();
            $decision->setReport($report);

            $this->em->persist($report);
            $this->em->flush();
        }

        $this->formatter->validateArray($data, [
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

    #[Route(path: '/decision/{id}', methods: ['GET'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function getOneById(Request $request, int $id): EntityDir\Report\Decision
    {
        $serialisedGroups = $request->query->has('groups') ? $request->query->all('groups') : ['decision'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $id, 'Decision with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        return $decision;
    }

    #[Route(path: '/decision/{id}', methods: ['DELETE'])]
    #[IsGranted(attribute: 'ROLE_DEPUTY')]
    public function deleteDecision(int $id): array
    {
        $decision = $this->findEntityBy(EntityDir\Report\Decision::class, $id, 'Decision with id:'.$id.' not found');
        $report = $decision->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($decision->getReport());

        $this->em->remove($decision);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }
}

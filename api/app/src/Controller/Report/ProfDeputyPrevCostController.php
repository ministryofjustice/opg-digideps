<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class ProfDeputyPrevCostController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_PROF_DEPUTY_COSTS];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
    }

    /**
     * @Route("/report/{reportId}/prof-deputy-previous-cost", methods={"POST"})
     *
     * @Security("is_granted('ROLE_PROF')")
     */
    public function addAction(Request $request, $reportId)
    {
        $data = $this->formatter->deserializeBodyContent($request);

        /* @var $report EntityDir\Report\Report */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        if (!array_key_exists('amount', $data)) {
            throw new \InvalidArgumentException('Missing amount');
        }
        $cost = new EntityDir\Report\ProfDeputyPreviousCost($report, $data['amount']);
        $this->updateEntity($data, $cost);
        $report->setProfDeputyCostsHasPrevious('yes');

        $this->em->persist($cost);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $cost->getId()];
    }

    /**
     * @Route("/prof-deputy-previous-cost/{id}", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_PROF')")
     */
    public function updateAction(Request $request, $id)
    {
        /** @var EntityDir\Report\ProfDeputyPreviousCost $cost */
        $cost = $this->findEntityBy(EntityDir\Report\ProfDeputyPreviousCost::class, $id);
        $report = $cost->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $cost);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $cost->getId()];
    }

    /**
     * @Route("/prof-deputy-previous-cost/{id}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_PROF')")
     *
     * @return object|null
     */
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['prof-deputy-costs-prev'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $cost = $this->findEntityBy(EntityDir\Report\ProfDeputyPreviousCost::class, $id, 'Prof Service Fee with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        return $cost;
    }

    /**
     * @Route("/report/{reportId}/prof-deputy-previous-cost/{id}", methods={"DELETE"})
     *
     * @Security("is_granted('ROLE_PROF')")
     */
    public function deleteProfDeputyPreviousCost($id)
    {
        $cost = $this->findEntityBy(EntityDir\Report\ProfDeputyPreviousCost::class, $id, 'Prof Service fee not found');
        $report = $cost->getReport(); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        $this->em->remove($cost);
        $this->em->flush();

        if (0 === count($report->getProfDeputyPreviousCosts())) {
            $report->setProfDeputyCostsHasPrevious(null);
        }

        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    /**
     * @return \App\Entity\Report\Report $report
     */
    private function updateEntity(array $data, EntityDir\Report\ProfDeputyPreviousCost $cost)
    {
        if (array_key_exists('start_date', $data)) {
            $cost->setStartDate(new \DateTime($data['start_date']));
        }

        if (array_key_exists('end_date', $data)) {
            $cost->setEndDate(new \DateTime($data['end_date']));
        }

        if (array_key_exists('amount', $data)) {
            $cost->setAmount($data['amount']);
        }

        return $cost;
    }
}

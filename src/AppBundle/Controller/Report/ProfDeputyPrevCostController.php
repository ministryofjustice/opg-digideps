<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ProfDeputyPrevCostController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_PROF_DEPUTY_COSTS];

    /**
     *
     * @Route("/report/{reportId}/prof-deputy-previous-cost")
     * @Method({"POST"})
     * @Security("has_role('ROLE_PROF')")
     */
    public function addAction(Request $request, $reportId)
    {
        $data = $this->deserializeBodyContent($request);

        /* @var $report EntityDir\Report\Report */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $cost = new EntityDir\Report\ProfDeputyPreviousCost($report);
        $this->updateEntity($data, $cost);
        $report->setProfDeputyCostsHasPrevious('yes');
        $this->persistAndFlush($cost);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $cost->getId()];
    }

    /**
     * @Route("/prof-deputy-previous-cost/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_PROF')")
     */
    public function updateAction(Request $request, $id)
    {
        /** @var EntityDir\Report\ProfDeputyPreviousCost $cost */
        $cost = $this->findEntityBy(EntityDir\Report\ProfDeputyPreviousCost::class, $id);
        $report = $cost->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $cost);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $cost->getId()];
    }

    /**
     * @Route("/prof-deputy-previous-cost/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_PROF')")

     * @param Request $request
     * @param $id
     *
     * @return null|object
     */
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['prof-deputy-costs-prev'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $cost = $this->findEntityBy(EntityDir\Report\ProfDeputyPreviousCost::class, $id, 'Prof Service Fee with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        return $cost;
    }

    /**
     * @Route("/report/{reportId}/prof-deputy-previous-cost/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_PROF')")
     */
    public function deleteProfDeputyPreviousCost($id)
    {
        $cost = $this->findEntityBy(EntityDir\Report\ProfDeputyPreviousCost::class, $id, 'Prof Service fee not found');
        $report = $cost->getReport(); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($cost->getReport());

        $this->getEntityManager()->remove($cost);

        if (count($report->getProfDeputyPreviousCosts())===0) {
            $report->setProfDeputyCostsHasPrevious(null);
        }

        $this->getEntityManager()->flush();
        //

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @param array                           $data
     * @param EntityDir\Report\ProfDeputyPreviousCost $cost
     *
     * @return \AppBundle\Entity\Report\Report $report
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

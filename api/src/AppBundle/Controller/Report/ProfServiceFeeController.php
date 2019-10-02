<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class ProfServiceFeeController extends RestController
{
    private $sectionIds = [EntityDir\Report\Report::SECTION_PROF_CURRENT_FEES];

    /**
     * @Route("/report/{reportId}/prof-service-fee", methods={"POST"})
     * @Security("has_role('ROLE_PROF')")
     */
    public function addAction(Request $request, $reportId)
    {
        $data = $this->deserializeBodyContent($request);

        /* @var $report EntityDir\Report\Report */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $profServiceFee = new EntityDir\Report\ProfServiceFeeCurrent($report);
        //TODO create a factory with ($data['fee_type_id'] value when/if needed
        $profServiceFee->setReport($report);
        $this->updateEntity($data, $profServiceFee);
        $report->setCurrentProfPaymentsReceived('yes');
        $this->persistAndFlush($profServiceFee);

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $profServiceFee->getId()];
    }

    /**
     * @Route("/prof-service-fee/{id}", methods={"PUT"})
     * @Security("has_role('ROLE_PROF')")
     */
    public function updateAction(Request $request, $id)
    {
        /** @var EntityDir\Report\ProfServiceFee $profServiceFee */
        $profServiceFee = $this->findEntityBy(EntityDir\Report\ProfServiceFee::class, $id);
        $report = $profServiceFee->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($profServiceFee->getReport());

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $profServiceFee);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return ['id' => $profServiceFee->getId()];
    }

    /**
     * @Route("/prof-service-fee/{id}", methods={"GET"})
     * @Security("has_role('ROLE_PROF')")

     * @param Request $request
     * @param $id
     *
     * @return null|object
     */
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['prof_service_fee'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $profServiceFee = $this->findEntityBy(EntityDir\Report\ProfServiceFee::class, $id, 'Prof Service Fee with id:' . $id . ' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($profServiceFee->getReport());

        return $profServiceFee;
    }

    /**
     * @Route("/prof-service-fee/{id}", methods={"DELETE"})
     * @Security("has_role('ROLE_PROF')")
     */
    public function deleteProfServiceFee($id)
    {
        $profServiceFee = $this->findEntityBy(EntityDir\Report\ProfServiceFee::class, $id, 'Prof Service fee not found');
        $report = $profServiceFee->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($profServiceFee->getReport());

        $this->getEntityManager()->remove($profServiceFee);
        $this->getEntityManager()->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->getEntityManager()->flush();

        return [];
    }

    /**
     * @param array                           $data
     * @param EntityDir\Report\ProfServiceFee $profServiceFee
     *
     * @return \AppBundle\Entity\Report\Report $report
     */
    private function updateEntity(array $data, EntityDir\Report\ProfServiceFee $profServiceFee)
    {
        if (array_key_exists('assessed_or_fixed', $data)) {
            $profServiceFee->setAssessedOrFixed($data['assessed_or_fixed']);
        }

        if (array_key_exists('fee_type_id', $data)) {
            $profServiceFee->setFeeTypeId($data['fee_type_id']);
        }

        if (array_key_exists('service_type_id', $data)) {
            $profServiceFee->setServiceTypeId($data['service_type_id']);
        }

        if (array_key_exists('amount_charged', $data)) {
            $profServiceFee->setAmountCharged($data['amount_charged']);
        }

        if (array_key_exists('payment_received', $data)) {
            $profServiceFee->setPaymentReceived($data['payment_received']);
            if ($profServiceFee->getPaymentReceived() == 'no') {
                $profServiceFee->setAmountReceived(null);
                $profServiceFee->setPaymentReceivedDate(null);
            } else {
                if (array_key_exists('amount_received', $data)) {
                    $profServiceFee->setAmountReceived($data['amount_received']);
                }

                if (array_key_exists('payment_received_date', $data)) {
                    $profServiceFee->setPaymentReceivedDate(new \DateTime($data['payment_received_date']));
                }
            }
        }

        return $profServiceFee;
    }
}

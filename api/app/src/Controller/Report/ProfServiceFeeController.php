<?php

namespace App\Controller\Report;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProfServiceFeeController extends RestController
{
    private array $sectionIds = [EntityDir\Report\Report::SECTION_PROF_CURRENT_FEES];

    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/report/{reportId}/prof-service-fee', methods: ['POST'])]
    #[Security("is_granted('ROLE_PROF')")]
    public function addAction(Request $request, $reportId)
    {
        $data = $this->formatter->deserializeBodyContent($request);

        /* @var $report EntityDir\Report\Report */
        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $profServiceFee = new EntityDir\Report\ProfServiceFeeCurrent($report);
        // TODO create a factory with ($data['fee_type_id'] value when/if needed
        $profServiceFee->setReport($report);
        $this->updateEntity($data, $profServiceFee);
        $report->setCurrentProfPaymentsReceived('yes');

        $this->em->persist($profServiceFee);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $profServiceFee->getId()];
    }

    #[Route(path: '/prof-service-fee/{id}', methods: ['PUT'])]
    #[Security("is_granted('ROLE_PROF')")]
    public function updateAction(Request $request, $id)
    {
        /** @var EntityDir\Report\ProfServiceFee $profServiceFee */
        $profServiceFee = $this->findEntityBy(EntityDir\Report\ProfServiceFee::class, $id);
        $report = $profServiceFee->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($profServiceFee->getReport());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $profServiceFee);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return ['id' => $profServiceFee->getId()];
    }

    /**
     * @return object|null
     */
    #[Route(path: '/prof-service-fee/{id}', methods: ['GET'])]
    #[Security("is_granted('ROLE_PROF')")]
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['prof_service_fee'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $profServiceFee = $this->findEntityBy(EntityDir\Report\ProfServiceFee::class, $id, 'Prof Service Fee with id:'.$id.' not found');
        $this->denyAccessIfReportDoesNotBelongToUser($profServiceFee->getReport());

        return $profServiceFee;
    }

    #[Route(path: '/prof-service-fee/{id}', methods: ['DELETE'])]
    #[Security("is_granted('ROLE_PROF')")]
    public function deleteProfServiceFee($id)
    {
        $profServiceFee = $this->findEntityBy(EntityDir\Report\ProfServiceFee::class, $id, 'Prof Service fee not found');
        $report = $profServiceFee->getReport();
        $this->denyAccessIfReportDoesNotBelongToUser($profServiceFee->getReport());

        $this->em->remove($profServiceFee);
        $this->em->flush();

        $report->updateSectionsStatusCache($this->sectionIds);
        $this->em->flush();

        return [];
    }

    /**
     * @return \App\Entity\Report\Report $report
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
            if ('no' == $profServiceFee->getPaymentReceived()) {
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

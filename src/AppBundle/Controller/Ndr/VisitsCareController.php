<?php

namespace AppBundle\Controller\Ndr;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends RestController
{
    /**
     * @Route("/ndr/visits-care")
     * @Method({"POST"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function addAction(Request $request)
    {
        $visitsCare = new EntityDir\Ndr\VisitsCare();
        $data = $this->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $data['ndr_id']);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $visitsCare->setNdr($ndr);

        $this->updateEntity($data, $visitsCare);

        $this->persistAndFlush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/ndr/visits-care/{id}")
     * @Method({"PUT"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function updateAction(Request $request, $id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Ndr\VisitsCare::class, $id);
        $this->denyAccessIfNdrDoesNotBelongToUser($visitsCare->getNdr());

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $visitsCare);

        $this->getEntityManager()->flush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/ndr/{ndrId}/visits-care")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $ndrId
     */
    public function findByNdrIdAction($ndrId)
    {
        $report = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($report);

        $ret = $this->getRepository(EntityDir\Ndr\Ndr::class)->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/ndr/visits-care/{id}")
     * @Method({"GET"})
     * @Security("has_role('ROLE_DEPUTY')")
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['visits-care'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy(EntityDir\Ndr\VisitsCare::class, $id, 'VisitsCare with id:' . $id . ' not found');
        $this->denyAccessIfNdrDoesNotBelongToUser($visitsCare->getNdr());

        return $visitsCare;
    }

    /**
     * @Route("/ndr/visits-care/{id}")
     * @Method({"DELETE"})
     * @Security("has_role('ROLE_DEPUTY')")
     */
    public function deleteVisitsCare($id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Ndr\VisitsCare::class, $id, 'VisitsCare not found'); /* @var $visitsCare EntityDir\Ndr\VisitsCare */
        $this->denyAccessIfNdrDoesNotBelongToUser($visitsCare->getNdr());

        $this->getEntityManager()->remove($visitsCare);
        $this->getEntityManager()->flush($visitsCare);

        return [];
    }

    /**
     * @param array                    $data
     * @param EntityDir\Ndr\VisitsCare $visitsCare
     *
     * @return EntityDir\Ndr\VisitsCare $report
     */
    private function updateEntity(array $data, EntityDir\Ndr\VisitsCare $visitsCare)
    {
        if (array_key_exists('plan_move_new_residence', $data)) {
            $visitsCare->setPlanMoveNewResidence($data['plan_move_new_residence']);
        }

        if (array_key_exists('plan_move_new_residence_details', $data)) {
            $visitsCare->setPlanMoveNewResidenceDetails($data['plan_move_new_residence_details']);
        }

        if (array_key_exists('do_you_live_with_client', $data)) {
            $visitsCare->setDoYouLiveWithClient($data['do_you_live_with_client']);
        }

        if (array_key_exists('does_client_receive_paid_care', $data)) {
            $visitsCare->setDoesClientReceivePaidCare($data['does_client_receive_paid_care']);
        }

        if (array_key_exists('how_often_do_you_contact_client', $data)) {
            $visitsCare->setHowOftenDoYouContactClient($data['how_often_do_you_contact_client']);
        }

        if (array_key_exists('how_is_care_funded', $data)) {
            $visitsCare->setHowIsCareFunded($data['how_is_care_funded']);
        }

        if (array_key_exists('who_is_doing_the_caring', $data)) {
            $visitsCare->setWhoIsDoingTheCaring($data['who_is_doing_the_caring']);
        }

        if (array_key_exists('does_client_have_a_care_plan', $data)) {
            $visitsCare->setDoesClientHaveACarePlan($data['does_client_have_a_care_plan']);
        }

        if (array_key_exists('when_was_care_plan_last_reviewed', $data)) {
            if (!empty($data['when_was_care_plan_last_reviewed'])) {
                $visitsCare->setWhenWasCarePlanLastReviewed(new \DateTime($data['when_was_care_plan_last_reviewed']));
            } else {
                $visitsCare->setWhenWasCarePlanLastReviewed(null);
            }
        }

        return $visitsCare;
    }
}

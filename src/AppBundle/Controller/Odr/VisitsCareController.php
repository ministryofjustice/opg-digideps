<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class VisitsCareController extends RestController
{
    /**
     * @Route("/odr/visits-care")
     * @Method({"POST"})
     */
    public function addAction(Request $request)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $visitsCare = new EntityDir\Odr\VisitsCare();
        $data = $this->deserializeBodyContent($request);

        $odr = $this->findEntityBy('Odr\Odr', $data['odr_id']);
        $this->denyAccessIfOdrDoesNotBelongToUser($odr);

        $visitsCare->setOdr($odr);

        $this->updateEntity($data, $visitsCare);

        $this->persistAndFlush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/odr/visits-care/{id}")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $visitsCare = $this->findEntityBy('Odr\VisitsCare', $id);
        $this->denyAccessIfOdrDoesNotBelongToUser($visitsCare->getOdr());

        $data = $this->deserializeBodyContent($request);
        $this->updateEntity($data, $visitsCare);

        $this->getEntityManager()->flush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @Route("/odr/{odrId}/visits-care")
     * @Method({"GET"})
     *
     * @param int $odrId
     */
    public function findByOdrIdAction($odrId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Odr\Odr', $odrId);
        $this->denyAccessIfOdrDoesNotBelongToUser($report);

        $ret = $this->getRepository('Odr\Odr')->findByReport($report);

        return $ret;
    }

    /**
     * @Route("/odr/visits-care/{id}")
     * @Method({"GET"})
     *
     * @param int $id
     */
    public function getOneById(Request $request, $id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $serialiseGroups = $request->query->has('groups') ? (array) $request->query->get('groups') : ['visits-care'];
        $this->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy('Odr\VisitsCare', $id, 'VisitsCare with id:'.$id.' not found');
        $this->denyAccessIfOdrDoesNotBelongToUser($visitsCare->getOdr());

        return $visitsCare;
    }

    /**
     * @Route("/odr/visits-care/{id}")
     * @Method({"DELETE"})
     */
    public function deleteVisitsCare($id)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $visitsCare = $this->findEntityBy('Odr\VisitsCare', $id, 'VisitsCare not found'); /* @var $visitsCare EntityDir\Odr\VisitsCare */
        $this->denyAccessIfOdrDoesNotBelongToUser($visitsCare->getOdr());

        $this->getEntityManager()->remove($visitsCare);
        $this->getEntityManager()->flush($visitsCare);

        return [];
    }

    /**
     * @param array                    $data
     * @param EntityDir\Odr\VisitsCare $visitsCare
     *
     * @return EntityDir\Odr\VisitsCare $report
     */
    private function updateEntity(array $data, EntityDir\Odr\VisitsCare $visitsCare)
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

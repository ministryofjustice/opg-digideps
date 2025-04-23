<?php

namespace App\Controller\Ndr;

use App\Controller\RestController;
use App\Entity as EntityDir;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class VisitsCareController extends RestController
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly RestFormatter $formatter)
    {
        parent::__construct($em);
    }

    #[Route(path: '/ndr/visits-care', methods: ['POST'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function addAction(Request $request)
    {
        $visitsCare = new EntityDir\Ndr\VisitsCare();
        $data = $this->formatter->deserializeBodyContent($request);

        $ndr = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $data['ndr_id']);
        $this->denyAccessIfNdrDoesNotBelongToUser($ndr);

        $visitsCare->setNdr($ndr);

        $this->updateEntity($data, $visitsCare);

        $this->em->persist($visitsCare);
        $this->em->flush();

        return ['id' => $visitsCare->getId()];
    }

    #[Route(path: '/ndr/visits-care/{id}', methods: ['PUT'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function updateAction(Request $request, $id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Ndr\VisitsCare::class, $id);
        $this->denyAccessIfNdrDoesNotBelongToUser($visitsCare->getNdr());

        $data = $this->formatter->deserializeBodyContent($request);
        $this->updateEntity($data, $visitsCare);

        $this->em->flush($visitsCare);

        return ['id' => $visitsCare->getId()];
    }

    /**
     * @param int $ndrId
     */
    #[Route(path: '/ndr/{ndrId}/visits-care', methods: ['GET'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function findByNdrIdAction($ndrId)
    {
        $report = $this->findEntityBy(EntityDir\Ndr\Ndr::class, $ndrId);
        $this->denyAccessIfNdrDoesNotBelongToUser($report);

        $ret = $this->em->getRepository(EntityDir\Ndr\Ndr::class)->findByReport($report);

        return $ret;
    }

    /**
     * @param int $id
     */
    #[Route(path: '/ndr/visits-care/{id}', methods: ['GET'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function getOneById(Request $request, $id)
    {
        $serialiseGroups = $request->query->has('groups') ? $request->query->all('groups') : ['visits-care'];
        $this->formatter->setJmsSerialiserGroups($serialiseGroups);

        $visitsCare = $this->findEntityBy(EntityDir\Ndr\VisitsCare::class, $id, 'VisitsCare with id:'.$id.' not found');
        $this->denyAccessIfNdrDoesNotBelongToUser($visitsCare->getNdr());

        return $visitsCare;
    }

    #[Route(path: '/ndr/visits-care/{id}', methods: ['DELETE'])]
    #[Security("is_granted('ROLE_DEPUTY')")]
    public function deleteVisitsCare($id)
    {
        $visitsCare = $this->findEntityBy(EntityDir\Ndr\VisitsCare::class, $id, 'VisitsCare not found'); /* @var $visitsCare EntityDir\Ndr\VisitsCare */
        $this->denyAccessIfNdrDoesNotBelongToUser($visitsCare->getNdr());

        $this->em->remove($visitsCare);
        $this->em->flush($visitsCare);

        return [];
    }

    /**
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

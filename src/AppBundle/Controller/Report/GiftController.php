<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use AppBundle\Entity as EntityDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class GiftController extends RestController
{
    /**
     * @Route("/report/{reportId}/gift/{giftId}", requirements={"reportId":"\d+", "giftId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById(Request $request, $reportId, $giftId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy(EntityDir\Report\Gift::class, $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());

        $serialisedGroups = $request->query->has('groups')
            ? (array) $request->query->get('groups') : ['gifts'];
        $this->setJmsSerialiserGroups($serialisedGroups);

        return $gift;
    }

    /**
     * @Route("/report/{reportId}/gift", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);
        $this->validateArray($data, [
            'explanation' => 'mustExist',
            'amount' => 'mustExist',
        ]);
        $gift = new EntityDir\Report\Gift($report);

        $this->updateEntityWithData($gift, $data);
        $report->setGiftsExist('yes');

        $this->persistAndFlush($gift);
        $this->persistAndFlush($report);

        return ['id' => $gift->getId()];
    }

    /**
     * @Route("/report/{reportId}/gift/{giftId}", requirements={"reportId":"\d+", "giftId":"\d+"})
     * @Method({"PUT"})
     */
    public function edit(Request $request, $reportId, $giftId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy(EntityDir\Report\Gift::class, $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());

        $this->updateEntityWithData($gift, $data);

        $this->getEntityManager()->flush($gift);

        return ['id' => $gift->getId()];
    }

    /**
     * @Route("/report/{reportId}/gift/{giftId}", requirements={"reportId":"\d+", "giftId":"\d+"})
     * @Method({"DELETE"})
     */
    public function delete($reportId, $giftId)
    {
        $this->denyAccessUnlessGranted(EntityDir\User::ROLE_LAY_DEPUTY);

        $report = $this->findEntityBy(EntityDir\Report\Report::class, $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy(EntityDir\Report\Gift::class, $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());
        $this->getEntityManager()->remove($gift);

        $this->getEntityManager()->flush();

        return [];
    }

    private function updateEntityWithData(EntityDir\Report\Gift $gift, array $data)
    {
        // common props
        $this->hydrateEntityWithArrayData($gift, $data, [
            'amount' => 'setAmount',
            'explanation' => 'setExplanation',
        ]);
    }
}

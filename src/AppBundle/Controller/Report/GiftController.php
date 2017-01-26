<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\RestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity as EntityDir;

class GiftController extends RestController
{
    /**
     * @Route("/report/{reportId}/gift/{giftId}", requirements={"reportId":"\d+", "giftId":"\d+"})
     * @Method({"GET"})
     */
    public function getOneById($reportId, $giftId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy('Report\Gift', $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());

        return $gift;
    }

    /**
     * @Route("/report/{reportId}/gift", requirements={"reportId":"\d+"})
     * @Method({"POST"})
     */
    public function add(Request $request, $reportId)
    {
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report\Report', $reportId); /* @var $report EntityDir\Report\Report */
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
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $data = $this->deserializeBodyContent($request);

        $report = $this->findEntityBy('Report\Report', $reportId);
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy('Report\Gift', $giftId);
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
        $this->denyAccessUnlessGranted(EntityDir\Role::LAY_DEPUTY);

        $report = $this->findEntityBy('Report\Report', $reportId); /* @var $report EntityDir\Report\Report */
        $this->denyAccessIfReportDoesNotBelongToUser($report);

        $gift = $this->findEntityBy('Report\Gift', $giftId);
        $this->denyAccessIfReportDoesNotBelongToUser($gift->getReport());
        $this->getEntityManager()->remove($gift);

        if (count($report->getGifts()) === 0) {
            $report->setGiftsExist(null); // reset choice
        }
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

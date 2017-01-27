<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class GiftController extends AbstractController
{
    private static $jmsGroups = [
        'gifts',
    ];

    /**
     * @Route("/report/{reportId}/gifts", name="gifts")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if (count($report->getGifts()) > 0 || $report->getGiftsExist() !== null) {
            return $this->redirectToRoute('gifts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/exist", name="gifts_exist")
     * @Template()
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(new FormDir\Report\GiftExistType(), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getGiftsExist()) {
                case 'yes':
                    return $this->redirectToRoute('gifts_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/'.$reportId, $data, ['gifts-exist']);
                    return $this->redirectToRoute('gifts_summary', ['reportId' => $reportId]);
            }
        }

        $backLink = $this->generateUrl('gifts', ['reportId' => $reportId]);
        if ($request->get('from') == 'summary') {
            $backLink = $this->generateUrl('gifts_summary', ['reportId' => $reportId]);
        }

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/add", name="gifts_add")
     * @Template()
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $gift = new EntityDir\Report\Gift();

        $form = $this->createForm(new FormDir\Report\GiftType(), $gift);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/'.$report->getId().'/gift', $data, ['gift']);

            return $this->redirect($this->generateUrl('gifts_add_another', ['reportId' => $reportId]));
        }

        $backLinkRoute = 'gifts_'.$request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';


        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/add_another", name="gifts_add_another")
     * @Template()
     */
    public function addAnotherAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $form = $this->createForm(new FormDir\AddAnotherRecordType('report-gifts'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            switch ($form['addAnother']->getData()) {
                case 'yes':
                    return $this->redirectToRoute('gifts_add', ['reportId' => $reportId, 'from' => 'add_another']);
                case 'no':
                    return $this->redirectToRoute('gifts_summary', ['reportId' => $reportId]);
            }
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/edit/{giftId}", name="gifts_edit")
     * @Template()
     */
    public function editAction(Request $request, $reportId, $giftId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $gift = $this->getRestClient()->get('report/'.$report->getId().'/gift/'.$giftId, 'Report\Gift');

        $form = $this->createForm(new FormDir\Report\GiftType(), $gift);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Record edited');

            $this->getRestClient()->put('report/'.$report->getId().'/gift/'.$gift->getId(), $data, ['gift']);

            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('gifts_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/summary", name="gifts_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if (count($report->getGifts()) === 0 && $report->getGiftsExist() === null) {
            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/{giftId}/delete", name="gifts_delete")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $giftId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        $this->getRestClient()->delete('report/'.$report->getId().'/gift/'.$giftId);

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Gift deleted'
        );

        return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
    }
}

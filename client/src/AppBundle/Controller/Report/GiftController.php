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
        'gifts-state',
        'account'
    ];

    /**
     * @Route("/report/{reportId}/gifts", name="gifts")
     * @Template("AppBundle:Report/Gift:start.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getGiftsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('gifts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/exist", name="gifts_exist")
     * @Template("AppBundle:Report/Gift:exist.html.twig")
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(FormDir\YesNoType::class, $report, [ 'field' => 'giftsExist', 'translation_domain' => 'report-gifts']
                                 );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getGiftsExist()) {
                case 'yes':
                    return $this->redirectToRoute('gifts_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->getRestClient()->put('report/' . $reportId, $data, ['gifts-exist']);
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
     * @Template("AppBundle:Report/Gift:add.html.twig")
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $gift = new EntityDir\Report\Gift();

        $form = $this->createForm(
            FormDir\Report\GiftType::class,
            $gift,
            [
                'user' => $this->getUser(),
                'report' => $report
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->getRestClient()->post('report/' . $report->getId() . '/gift', $data, ['gift', 'account']);

            if ($form->getClickedButton()->getName() === 'saveAndAddAnother') {
                return $this->redirect($this->generateUrl('gifts_add', ['reportId' => $reportId, 'from' => $request->get('from')]));
            } else {
                return $this->redirect($this->generateUrl('gifts_summary', ['reportId' => $reportId]));
            }
        }

        $backLinkRoute = 'gifts_' . $request->get('from');
        $backLink = $this->routeExists($backLinkRoute) ? $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]) : '';

        return [
            'backLink' => $backLink,
            'form' => $form->createView(),
            'report' => $report
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/edit/{giftId}", name="gifts_edit")
     * @Template("AppBundle:Report/Gift:edit.html.twig")
     */
    public function editAction(Request $request, $reportId, $giftId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $gift = $this->getRestClient()->get(
            'report/' . $report->getId() . '/gift/' . $giftId,
            'Report\Gift',
            [
                'gifts',
                'account'
            ]
        );

        if ($gift->getBankAccount() instanceof EntityDir\Report\BankAccount) {
            $gift->setBankAccountId($gift->getBankAccount()->getId());
        }

        $form = $this->createForm(
            FormDir\Report\GiftType::class,
            $gift,
            [
                'user' => $this->getUser(),
                'report' => $report
            ]
        );
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Gift edited');

            $this->getRestClient()->put(
                'report/' . $report->getId() . '/gift/' . $gift->getId(),
                $data,
                ['gift', 'account']
            );

            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        return [
            'backLink' => $this->generateUrl('gifts_summary', ['reportId' => $reportId]),
            'form' => $form->createView(),
            'report' => $report
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/summary", name="gifts_summary")
     * @Template("AppBundle:Report/Gift:summary.html.twig")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getGiftsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/{giftId}/delete", name="gifts_delete")
     * @Template("AppBundle:Common:confirmDelete.html.twig")
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $giftId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isValid()) {
            $this->getRestClient()->delete('report/' . $report->getId() . '/gift/' . $giftId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Gift deleted'
            );

            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        $gift = $this->getRestClient()->get('report/' . $reportId . '/gift/' . $giftId, 'Report\\Gift');

        return [
            'translationDomain' => 'report-gifts',
            'report' => $report,
            'form' => $form->createView(),
            'summary' => [
                ['label' => 'deletePage.summary.explanation', 'value' => $gift->getExplanation()],
                ['label' => 'deletePage.summary.amount', 'value' => $gift->getAmount(), 'format' => 'money'],
            ],
            'backLink' => $this->generateUrl('gifts', ['reportId' => $reportId]),
        ];
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'gifts';
    }
}

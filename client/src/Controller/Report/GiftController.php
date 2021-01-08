<?php

namespace App\Controller\Report;

use App\Controller\AbstractController;
use App\Entity as EntityDir;
use App\Form as FormDir;

use App\Service\Client\Internal\ReportApi;
use App\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class GiftController extends AbstractController
{
    private static $jmsGroups = [
        'gifts',
        'gifts-state',
        'account'
    ];

    /** @var RestClient */
    private $restClient;

    /** @var ReportApi */
    private $reportApi;

    public function __construct(
        RestClient $restClient,
        ReportApi $reportApi
    ) {
        $this->restClient = $restClient;
        $this->reportApi = $reportApi;
    }

    /**
     * @Route("/report/{reportId}/gifts", name="gifts")
     * @Template("@App/Report/Gift/start.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function startAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($report->getStatus()->getGiftsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('gifts_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/exist", name="gifts_exist")
     * @Template("@App/Report/Gift/exist.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function existAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $form = $this->createForm(
            FormDir\YesNoType::class,
            $report,
            [ 'field' => 'giftsExist', 'translation_domain' => 'report-gifts']
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /* @var $data EntityDir\Report\Report */
            switch ($data->getGiftsExist()) {
                case 'yes':
                    return $this->redirectToRoute('gifts_add', ['reportId' => $reportId, 'from'=>'exist']);
                case 'no':
                    $this->restClient->put('report/' . $reportId, $data, ['gifts-exist']);
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
     * @Template("@App/Report/Gift/add.html.twig")
     *
     * @param Request $request
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function addAction(Request $request, $reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
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

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data->setReport($report);

            $this->restClient->post('report/' . $report->getId() . '/gift', $data, ['gift', 'account']);

            if ($form->getClickedButton()->getName() === 'saveAndAddAnother') {
                return $this->redirect($this->generateUrl('gifts_add', ['reportId' => $reportId, 'from' => $request->get('from')]));
            } else {
                return $this->redirect($this->generateUrl('gifts_summary', ['reportId' => $reportId]));
            }
        }

        try {
            $backLinkRoute = 'gifts_' . $request->get('from');
            $backLink = $this->generateUrl($backLinkRoute, ['reportId'=>$reportId]);

            return [
                'backLink' => $backLink,
                'form' => $form->createView(),
                'report' => $report
            ];
        } catch (RouteNotFoundException $e) {
            return [
                'backLink' => null,
                'form' => $form->createView(),
                'report' => $report
            ];
        }
    }

    /**
     * @Route("/report/{reportId}/gifts/edit/{giftId}", name="gifts_edit")
     * @Template("@App/Report/Gift/edit.html.twig")
     *
     * @param Request $request
     * @param $reportId
     * @param $giftId
     *
     * @return array|RedirectResponse
     */
    public function editAction(Request $request, $reportId, $giftId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $gift = $this->restClient->get(
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

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $request->getSession()->getFlashBag()->add('notice', 'Gift edited');

            $this->restClient->put(
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
     * @Template("@App/Report/Gift/summary.html.twig")
     *
     * @param $reportId
     *
     * @return array|RedirectResponse
     */
    public function summaryAction($reportId)
    {
        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getGiftsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/gifts/{giftId}/delete", name="gifts_delete")
     * @Template("@App/Common/confirmDelete.html.twig")
     *
     * @param Request $request
     * @param $reportId
     * @param $giftId
     *
     * @return array|RedirectResponse
     */
    public function deleteAction(Request $request, $reportId, $giftId)
    {
        $form = $this->createForm(FormDir\ConfirmDeleteType::class);
        $form->handleRequest($request);

        $report = $this->reportApi->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->restClient->delete('report/' . $report->getId() . '/gift/' . $giftId);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Gift deleted'
            );

            return $this->redirect($this->generateUrl('gifts', ['reportId' => $reportId]));
        }

        $gift = $this->restClient->get('report/' . $reportId . '/gift/' . $giftId, 'Report\\Gift');

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

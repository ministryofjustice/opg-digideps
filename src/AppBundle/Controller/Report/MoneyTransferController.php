<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransferController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/transfers/edit", name="transfers")
     *
     * @param int $reportId
     *
     * @return array
     */
    public function index(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account', 'money-transfer']);
        if (($nofAccounts = count($report->getAccounts())) < 2) {
            return $this->render('AppBundle:Report/MoneyTransfer:index_unhappy.html.twig', [
                    'report' => $report,
                    'subsection' => 'transfers',
                    'nOfAccounts' => $nofAccounts,
            ]);
        }

        $transfer = new EntityDir\Report\MoneyTransfer();
        $form = $this->createForm(new FormDir\Report\TransferType($report->getAccounts()), $transfer);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getRestClient()->post('report/'.$report->getId().'/money-transfers', $form->getData());

            return $this->redirect($this->generateUrl('transfers', ['reportId' => $reportId]));
        }

        return $this->render('AppBundle:Report/MoneyTransfer:index.html.twig', [
                'report' => $report,
                'subsection' => 'transfers',
                'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/report/{reportId}/transfers/{transferId}", name="transfers_delete")
     * @Method({"GET"})
     */
    public function delete($reportId, $transferId)
    {
        $this->getRestClient()->delete('report/'.$reportId.'/money-transfers/'.$transferId);

        return $this->redirect($this->generateUrl('transfers', ['reportId' => $reportId]));
    }

    /**
     * Sub controller action called when the no transfers form is embedded in another page.
     *
     * @Template("AppBundle:Report/MoneyTransfer:_noTransfers.html.twig")
     */
    public function _noTransfersPartialAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account', 'money-transfer']);

        $form = $this->createForm(new FormDir\Report\NoTransfersToAddType(), $report, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $request->getMethod() == 'POST' && $form->isValid()) {
            $this->getRestClient()->put('report/'.$reportId, [
                'no_transfers_to_add' => $form->getData()->getNoTransfersToAdd(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_save_json")
     * @Method({"POST", "PUT"})
     *
     * @param Request $request
     * @param int     $reportId
     *
     * @return JsonResponse
     */
    public function saveJson(Request $request, $reportId)
    {
        $data = [
            'account_from_id' => $request->get('account')[0],
            'account_to_id' => $request->get('account')[1],
            'amount' => $request->get('amount'),
        ];

        try {
            if (!$request->isXmlHttpRequest()) {
                throw new \RuntimeException('Endpoint only callable via AJAX');
            }

            if ($request->getMethod() == 'PUT') {
                $id = $request->get('id');
                $this->getRestClient()->put("report/{$reportId}/money-transfers/{$id}", $data);

                return new JsonResponse(['success' => true]);
            } else { //POST
                $createdTransferId = $this->getRestClient()->post("report/{$reportId}/money-transfers", $data);

                return new JsonResponse(['success' => true, 'transferId' => $createdTransferId]);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_delete_json")
     * @Method({"DELETE"})
     *
     * @param Request $request
     * @param int     $reportId
     * @param int     $transferId
     *                            return JsonResponse
     */
    public function deleteJson(Request $request, $reportId)
    {
        try {
            $this->getRestClient()->delete('report/'.$reportId.'/money-transfers/'.$request->get('id'));

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }
    }

    /**
     * @Route("/report/{reportId}/notransfers", name="transfers_no_transfers_json")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param int     $reportId
     *                          return JsonResponse
     */
    public function noTransfersJson(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['account', 'money-transfer']);

        $form = $this->createForm(new FormDir\Report\NoTransfersToAddType(), $report, []);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$reportId, [
                'no_transfers_to_add' => $form->getData()->getNoTransfersToAdd(),
            ]);
        }

        return new JsonResponse(['success' => true]);
    }
}

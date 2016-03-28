<?php
namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class TransfersController extends AbstractController
{

    /**
     * @Route("/report/{reportId}/transfers/edit", name="transfers")
     * @param integer $reportId
     * @return array
     */
    public function transfersAction(Request $request, $reportId)
    {
        $report = $this->getReport($reportId, ['basic', 'client', 'accounts', 'transfers']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        if (count($report->getAccounts()) < 2) {
            return $this->render('AppBundle:Transfers:transfers_unhappy.html.twig',[
                'report' => $report,
                'subsection' => 'transfers'
            ]);
        }

        $transfer = new EntityDir\MoneyTransfer();
        $form = $this->createForm(new FormDir\TransferType($report->getAccounts()), $transfer);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->get('restClient')->post('report/' .  $report->getId() . '/money-transfers', $form->getData());

            return $this->redirect($this->generateUrl('transfers', ['reportId' => $reportId]));
        }

        return $this->render('AppBundle:Transfers:transfers.html.twig',[
            'report' => $report,
            'subsection' => 'transfers',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_get_json")
     * @Method({"GET"})
     * @param Request $request
     * @param integer $reportId
     *
     * @return JsonResponse
     */
    public function transfersGetJson(Request $request, $reportId)
    {
        try {
            $data = $this->getRestClient()->get("report/{$reportId}", 'array', [ 'query' => [ 'groups' => 'transfers']]);
        } catch (\Exception $e) {
           return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }

        return new JsonResponse([
            'success' => true,
            'noTransfers' => (array_key_exists ( "no_transfers_to_add" , $data ) ? $data['no_transfers_to_add'] : false ),
            'transfers' => $data['money_transfers']
        ]);
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_add_json")
     * @Method({"POST"})
     * @param Request $request
     * @param integer $reportId
     *
     * @return JsonResponse
     */
    public function transfersSaveJson(Request $request, $reportId)
    {
        try {

            $data = json_decode($request->getContent(), true)['transfer'];
            $transferUpdated = $this->get('restClient')->post('report/' . $reportId . '/money-transfers', $data);

            if (array_key_exists ( "temporaryId", $data)) {
                $transferUpdated['temporaryId'] = $data['temporaryId'];
            }

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }

        return new JsonResponse(['transfer' => $transferUpdated]);
    }

    /**
     * @Route("/report/{reportId}/transfers/{transferId}", name="transfers_update_json")
     * @Method({"PUT"})
     * @param Request $request
     * @param integer $reportId
     * @param integer $transferId
     * return JsonResponse
     */
    public function transfersUpdateJson(Request $request, $reportId, $transferId)
    {
        try {
            $data = json_decode($request->getContent(), true)['transfer'];
            $transferUpdated = $this->get('restClient')->put('report/' . $reportId . '/money-transfers/' . $transferId, $data);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }
        return new JsonResponse(['transfer' => $transferUpdated]);
    }


    /**
     * @Route("/report/{reportId}/transfers/{transferId}", name="transfers_delete_json")
     * @Method({"DELETE"})
     * @param Request $request
     * @param integer $reportId
     * @param integer $transferId
     * return JsonResponse
     */
    public function transfersDeleteJson(Request $request, $reportId, $transferId)
    {
         try {
            $ret = $this->get('restClient')->delete('report/' . $reportId . '/money-transfers/' . $transferId);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }

        return new JsonResponse($ret);
    }

     /**
     * @Route("/report/{reportId}/transfers/{transferId}", name="transfers_delete")
     * @Method({"GET"})
     */
    public function transfersDelete(Request $request, $reportId, $transferId)
    {
        $this->get('restClient')->delete('report/' . $reportId . '/money-transfers/' . $transferId);

        return $this->redirect($this->generateUrl('transfers', ['reportId' => $reportId]));
    }

    /**
     * @Route("/report/{reportId}/notransfers", name="transfers_update_none")
     * @Method({"PUT"})
     * @param Request $request
     * @param integer $reportId
     * return JsonResponse
     */
     public function transfersUpdateNone(Request $request, $reportId)
    {
        $noTransfersToAdd = json_decode($request->getContent(), true)['noTransfers'];

        try {
            $this->getRestClient()->put('report/' . $reportId, [
                'no_transfers_to_add' => $noTransfersToAdd,
            ]);
            return new JsonResponse(['success'=>true]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'exception' => $e->getMessage()], 500);
        }


    }

    /**
     * Sub controller action called when the no transfers form is embedded in another page.
     *
     * @Template("AppBundle:Transfers:_noTransfers.html.twig")
     */
    public function _noTransfersPartialAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transfers', 'basic', 'client']);

        $form = $this->createForm(new FormDir\NoTransfersToAddType(), $report, []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $request->getMethod() == "POST" && $form->isValid()) {
            $this->getRestClient()->put('report/' . $reportId, [
                'no_transfers_to_add' => $form->getData()->getNoTransfersToAdd(),
            ]);
        }

        return [
            'form' => $form->createView(),
            'report' => $report
        ];
    }

}

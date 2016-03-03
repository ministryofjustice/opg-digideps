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
     * @Template()
     * @return array
     */
    public function transfersAction($reportId)
    {
        $report = $this->getReport($reportId, ['basic', 'client', 'accounts']);
        if ($report->getSubmitted()) {
            throw new \RuntimeException("Report already submitted and not editable.");
        }

        return [
            'report' => $report,
            'subsection' => 'moneyin'
        ];
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_get_json")
     * @Method({"GET"})
     * @param Request $request
     * @param integer $reportId
     * return JsonResponse
     */
    public function transfersGetJson(Request $request, $reportId)
    {
        $data = $this->getRestClient()->get("report/{$reportId}", 'array', [ 'query' => [ 'groups' => 'transfers']]);

        //return new JsonResponse($data['money_transfers']);
        return new JsonResponse(array('transfers'=>[]));
    }

    /**
     * @Route("/report/{reportId}/transfers", name="transfers_add_json")
     * @Method({"POST"})
     * @param Request $request
     * @param integer $reportId
     * return JsonResponse
     */
    public function transfersSaveJson(Request $request, $reportId)
    {
        echo $request->getContent();die;
        // {"reportId":1,"id":999999,"accountFrom":{"id":2,"bank":"barc","account_type":"cash","sort_code":"111111","account_number":"1234","opening_date":"2016-03-02T00:00:00+0000","opening_balance":"120.00","closing_balance":"2.00","created_at":"2016-03-02T10:41:16+0000"},"accountTo":null,"amount":null}

        $this->get('restClient')->post('report/' . $reportId . '/money-transfers', [
            'from_account_id' => 1,
            'to_account_id' => 2,
            'amount' => 3
        ]);

        return new JsonResponse([]);
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
        echo $request->getContent();die;

        $this->get('restClient')->put('report/' . $reportId . '/money-transfers/' . $transferId, [
            'from_account_id' => 1,
            'to_account_id' => 2,
            'amount' => 3
        ]);

        //
        return new JsonResponse([]);
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
        return new JsonResponse([]);
    }
}

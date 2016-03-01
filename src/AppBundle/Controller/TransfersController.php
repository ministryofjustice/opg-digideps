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
     * @Route("/report/{reportId}/transfers", name="transfers")
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
     * @Route("/report/{reportId}/transfers.json", name="transfers_save_json")
     * @Method({"PUT"})
     * @param Request $request
     * @param integer $reportId
     * return JsonResponse
     */
    public function transfersSaveJson(Request $request, $reportId)
    {
        return new JsonResponse(['success' => true]);
    }
}

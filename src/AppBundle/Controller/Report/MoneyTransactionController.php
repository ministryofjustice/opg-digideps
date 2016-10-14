<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MoneyTransactionController extends AbstractController
{
    /**
     * @Route("/report/{reportId}/accounts/moneyin", name="money_in")
     *
     * @param int     $reportId
     * @param Request $request
     * @Template()
     *
     * @return array
     */
    public function moneyinAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactionsIn', 'balance']);
        $form = $this->createForm(new FormDir\Report\TransactionsType('transactionsIn'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), ['transactionsIn']);

            return $this->redirect($this->generateUrl('money_in', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'moneyin',
            'jsonEndpoint' => 'transactionsIn',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/moneyout", name="money_out")
     *
     * @param int     $reportId
     * @param Request $request
     * @Template()
     *
     * @return array
     */
    public function moneyoutAction(Request $request, $reportId)
    {
        $report = $this->getReportIfReportNotSubmitted($reportId, ['transactionsOut', 'balance']);

        $form = $this->createForm(new FormDir\Report\TransactionsType('transactionsOut'), $report);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), ['transactionsOut']);

            return $this->redirect($this->generateUrl('money_out', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
            'subsection' => 'moneyout',
            'jsonEndpoint' => 'transactionsOut',
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/report/{reportId}/accounts/{type}.json", name="accounts_money_save_json",
     *     requirements={"type"="transactionsIn|transactionsOut"}
     * )
     * @Method({"PUT"})
     *
     * @param Request $request
     * @param int     $reportId
     * @param string  $type
     *
     * 1000 - Already submitted
     * 1001 - Form field error
     * 1002 - Exception
     * 1003 - Error saving
     *
     * @return JsonResponse
     */
    public function moneySaveJson(Request $request, $reportId, $type)
    {
        try {
            $report = $this->getReport($reportId, [$type, 'balance']);
            if ($report->getSubmitted()) {
                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'errorCode' => 1000,
                        'errorDescription' => 'Unable to change submitted report ',
                    ],
                ], 500);
            }

            $form = $this->createForm(new FormDir\Report\TransactionsType($type), $report, ['method' => 'PUT']);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                $errorsArray = $this->get('formErrorsFormatter')->toArray($form);

                return new JsonResponse([
                    'success' => false,
                    'errors' => [
                        'errorCode' => 1001,
                        'errorDescription' => 'Form validation error',
                        'fields' => $errorsArray,
                    ],
                ], 500);
            }

            $this->getRestClient()->put('report/'.$report->getId(), $form->getData(), [$type]);

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'errors' => [
                    'errorCode' => 1002,
                    'errorDescription' => $e->getMessage(),
                ],
            ], 500);
        }
    }
}

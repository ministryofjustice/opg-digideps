<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-deputy-costs")
 */
class ProfDeputyCostsController extends AbstractController
{
    private static $jmsGroups = [
        'status',
        'deputy-costs-how-charged',
    ];

    /**
     * @Route("", name="prof_deputy_costs")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_deputy_costs_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/how-charged", name="prof_deputy_costs_how_charged")
     * @Template()
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromSummaryPage = $request->get('from') == 'summary';

        $form = $this->createForm(FormDir\Report\ProfDeputyCostHowType::class, $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/' . $reportId, $data, ['deputyCostsHowCharged']);

            $route = $fromSummaryPage ? 'prof_deputy_costs_summary' : 'prof_deputy_costs';

            return $this->redirectToRoute($route, ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl($fromSummaryPage ? 'money_in_short_summary' : 'money_in_short', ['reportId'=>$reportId])
        ];
    }

    /**
     * @Route("/summary", name="prof_deputy_costs_summary")
     * @Template()
     *
     * @param int $reportId
     *
     * @return array
     */
    public function summaryAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsState()['state'] == EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirect($this->generateUrl('prof_deputy_costs', ['reportId' => $reportId]));
        }

        return [
            'report' => $report,
        ];
    }


    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCosts';
    }
}

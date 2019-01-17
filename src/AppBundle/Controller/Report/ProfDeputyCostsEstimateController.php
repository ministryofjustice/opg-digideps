<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Entity\Report\Report;
use AppBundle\Form as FormDir;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base route
 *
 * @Route("/report/{reportId}/prof-deputy-costs-estimate")
 */
class ProfDeputyCostsEstimateController extends AbstractController
{
    private static $jmsGroups = [
        'status',
        'prof-deputy-costs-estimate-how-charged'
    ];

    /**
     * @Route("", name="prof_deputy_costs_estimate")
     * @Template()
     *
     * @param $reportId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function startAction($reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        if ($report->getStatus()->getProfDeputyCostsEstimateState()['state'] != EntityDir\Report\Status::STATE_NOT_STARTED) {
            return $this->redirectToRoute('prof_deputy_costs_estimate_summary', ['reportId' => $reportId]);
        }

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/how-charged", name="prof_deputy_costs_estimate_how_charged")
     * @Template()
     *
     * @param Request $request
     * @param $reportId
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function howChargedAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, ['prof-deputy-costs-estimate-how-charged']);

        $form = $this->createForm(FormDir\Report\ProfDeputyCostsEstimateHowType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->persistUpdate($reportId, $form->getData(), ['deputyCostsEstimateHowCharged']);

            return $this->redirectToRoute(
                $this->determineNextRouteFromHowCharged($request, $form),
                ['reportId'=>$reportId]
            );
        }

        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('prof_deputy_costs_estimate', ['reportId'=>$reportId])
        ];
    }

    /**
     * @param $id
     * @param Report $report
     * @param array $groups
     */
    private function persistUpdate($id, Report $report, array $groups)
    {
        $this->getRestClient()->put('report/' . $id, $report, $groups);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @return string
     */
    private function determineNextRouteFromHowCharged(Request $request, FormInterface $form)
    {
        $howCharged = $form->getData()->getProfDeputyCostsEstimateHowCharged();

        return ($request->get('from') === 'summary' || $howCharged === Report::PROF_DEPUTY_COSTS_ESTIMATE_TYPE_FIXED) ?
            'prof_deputy_costs_estimate_summary' :
            'prof_deputy_costs_estimate_breakdown';
    }

    /**
     * @return string
     */
    protected function getSectionId()
    {
        return 'profDeputyCosts';
    }
}

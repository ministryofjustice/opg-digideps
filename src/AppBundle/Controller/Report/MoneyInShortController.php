<?php

namespace AppBundle\Controller\Report;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Odr\Odr;
use AppBundle\Form as FormDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\SectionValidator\Odr\IncomeBenefitsValidator;
use AppBundle\Service\StepRedirector;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class MoneyInShortController extends AbstractController
{
    private static $jmsGroups = [
        'client-cot',
        'money-short-categories-in',
    ];

    /**
     * @Route("/report/{reportId}/money-in-short", name="money_in_short")
     * @Template()
     */
    public function startAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        //TODO redirect logic

        return [
            'report' => $report,
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/category", name="money_in_short_category")
     * @Template()
     */
    public function categoryAction(Request $request, $reportId)
    {
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);
        $fromPage = $request->get('from');

        $form = $this->createForm(new FormDir\Report\MoneyShortType(), $report);
        $form->handleRequest($request);

        if ($form->get('save')->isClicked() && $form->isValid()) {
            $data = $form->getData();

            $this->getRestClient()->put('report/'.$reportId, $data, ['money-short-categories-in']);

            if ($fromPage == 'summary') {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Answer edited'
                );
            }

            return $this->redirectToRoute('money_in_short_summary', ['reportId'=>$reportId]);
        }


        return [
            'report' => $report,
            'form' => $form->createView(),
            'backLink' => $this->generateUrl('money_in_short', ['reportId'=>$reportId]),
            'skipLink' => $this->generateUrl('money_in_short', ['reportId'=>$reportId]),
        ];
    }

    /**
     * @Route("/report/{reportId}/money-in-short/summary", name="money_in_short_summary")
     * @Template()
     */
    public function summaryAction(Request $request, $reportId)
    {
        $fromPage = $request->get('from');
        $report = $this->getReportIfNotSubmitted($reportId, self::$jmsGroups);

        // not started -> go back to start page
//        $oss = new OdrStatusService($report);
//        if ($oss->getIncomeBenefitsState() == OdrStatusService::STATE_NOT_STARTED && $fromPage != 'skip-step' && $fromPage != 'last-step') {
//            return $this->redirectToRoute('money_in_short', ['reportId' => $reportId]);
//        }

        return [
            'comingFromLastStep' => $fromPage == 'skip-step' || $fromPage == 'last-step',
            'report' => $report,
//            'validator' => new IncomeBenefitsValidator($report),
        ];
    }
}

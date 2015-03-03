<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Decision;
use AppBundle\Form\AddDecision;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Service\ApiClient;

/**
 * @Route("/report")
 */
class DecisionController extends Controller
{

    /**
     * @Route("/decision/{reportId}", name="add_decision")
     * @Template("AppBundle:Decision:list.html.twig")
     */
    public function addAction(Request $request, $reportId)
    {
        $apiClient = $this->get('apiclient'); /* @var $apiClient ApiClient */
        
        // just needed for title etc,
        $report = $apiClient->getEntity('Report', 'find_report_by_id', [ 'query' => [ 'id' => $reportId ]]);

        $form = $this->createForm(new AddDecision([
            'clientInvolvedBooleanEmptyValue' => $this->get('translator')->trans('clientInvolvedBoolean.defaultOption', [], 'decision')
        ]), new Decision($reportId));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // add decision
                $response = $apiClient->postC('add_decision', $form->getData());
                
                return $this->redirect($this->generateUrl('add_decision', ['reportId'=>$reportId]));
            }
        }

        return [
            'decisions' => $apiClient->getEntities('Decision', 'find_decision_by_report_id', [ 'query' => [ 'reportId' => $reportId ]]),
            'form' => $form->createView(),
            'report' => $report,
            'client' => [], //to pass
        ];
    }

}
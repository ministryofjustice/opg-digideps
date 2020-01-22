<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Form\Admin\Fixture\CourtOrderFixtureType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/fixtures")
 */
class FixtureController extends AbstractController
{
    /**
     * @Route("/court-orders", name="admin_fixtures_court_orders")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @Template("AppBundle:Admin/Fixtures:courtOrders.html.twig")
     */
    public function courtOrdersAction(Request $request)
    {
        if ($this->get('kernel')->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CourtOrderFixtureType::class, null, [
            'deputyType' => $request->get('deputy-type', User::TYPE_LAY),
            'reportType' => $request->get('report-type', Report::TYPE_HEALTH_WELFARE),
            'reportStatus' => $request->get('report-status', Report::STATUS_NOT_STARTED)
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = $form->getData();
            $courtDate = $request->get('court-date') ? new \DateTime($request->get('court-date')) : new \DateTime('2017-02-01');
            $deputyEmail = $request->query->get('deputy-email', sprintf('%s-deputy-%s@fixture.com', strtolower($submitted['deputyType']), mt_rand(1000, 9999)));
            $randomCaseNumber = str_pad(rand(00000001,99999999), 8, "0", STR_PAD_LEFT);

            $this->getRestClient()->post('v2/fixture/court-order', json_encode([
                'deputyType' => $submitted['deputyType'],
                'deputyEmail' => $deputyEmail,
                'caseNumber' =>  $request->get('case-number', $randomCaseNumber),
                'reportType' => $submitted['reportType'],
                'reportStatus' => $submitted['reportStatus'],
                'courtDate' => $courtDate->format('Y-m-d')
            ]));

            $request->getSession()->getFlashBag()->add('notice', "Created deputy with email: $deputyEmail");
        }

        return ['form' => $form->createView()];
    }
}

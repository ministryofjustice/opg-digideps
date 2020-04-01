<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\User;
use AppBundle\Form\Admin\Fixture\CourtOrderFixtureType;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
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
    public function courtOrdersAction(Request $request, KernelInterface $kernel)
    {
        if ($kernel->getEnvironment() === 'prod') {
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
            $randomCaseNumber = str_pad(rand(1,99999999), 8, "0", STR_PAD_LEFT);

            $this->getRestClient()->post('v2/fixture/court-order', json_encode([
                'deputyType' => $submitted['deputyType'],
                'deputyEmail' => $deputyEmail,
                'caseNumber' =>  $request->get('case-number', $randomCaseNumber),
                'reportType' => $submitted['reportType'],
                'reportStatus' => $submitted['reportStatus'],
                'courtDate' => $courtDate->format('Y-m-d')
            ]));

            $this->addFlash('notice', "Created deputy with email: $deputyEmail");
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/complete-sections/{reportId}", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @param Request $request
     * @param $reportId
     * @return JsonResponse
     */
    public function completeReportSectionsAction(Request $request, $reportId, KernelInterface $kernel): JsonResponse
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $sections = $request->get('sections');

        $this
            ->getRestClient()
            ->put("v2/fixture/complete-sections/$reportId?sections=$sections", []);

        return new JsonResponse(['Report updated']);
    }

    /**
     * @Route("/createAdmin", methods={"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     */
    public function createAdmin(Request $request, KernelInterface $kernel)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $this
            ->getRestClient()
            ->post("v2/fixture/createAdmin", json_encode([
                "adminType" => $request->query->get('adminType'),
                "email" => $request->query->get('email'),
                "firstName" => $request->query->get('firstName'),
                "lastName" => $request->query->get('lastName'),
                "activated" => $request->query->get('activated')
            ]));

        return new Response();
    }

    /**
     * @Route("/getUserIDByEmail/{email}", methods={"GET"})
     * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     */
    public function getUserIDByEmail(KernelInterface $kernel, string $email)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        /** @var array $response */
        $response = json_decode($this
            ->getRestClient()
            ->get("v2/fixture/getUserIDByEmail/$email", 'response')->getBody(), true);

        if ($response['success']) {
            return new Response($response['data']['id']);
        } else {
            return new Response($response['message'], Response::HTTP_NOT_FOUND);
        }
    }



    /**
     * @Route("/createUser", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createUser(Request $request, KernelInterface $kernel)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $this
            ->getRestClient()
            ->post("v2/fixture/createUser", json_encode([
                "ndr" => $request->query->get('ndr'),
                "deputyType" => $request->query->get('deputyType'),
                "deputyEmail" => $request->query->get('email'),
                "firstName" => $request->query->get('firstName'),
                "lastName" => $request->query->get('lastName'),
                "postCode" => $request->query->get('postCode'),
                "activated" => $request->query->get('activated')
            ]));

        return new Response();
    }

    /**
     * @Route("/createClientAttachDeputy", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function createClientAndAttachToDeputy(Request $request, KernelInterface $kernel)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        try {
            $this
                ->getRestClient()
                ->post("v2/fixture/createClientAttachDeputy",
                    json_encode([
                        "firstName" => $request->query->get('firstName'),
                        "lastName" => $request->query->get('lastName'),
                        "phone" => $request->query->get('phone'),
                        "address" => $request->query->get('address'),
                        "address2" => $request->query->get('address2'),
                        "county" => $request->query->get('county'),
                        "postCode" => $request->query->get('postCode'),
                        "caseNumber" => $request->query->get('caseNumber'),
                        "deputyEmail" => $request->query->get('deputyEmail')]
                    )
                );
        } catch(\Throwable $e) {
            throw $e;
        }

        return new Response();
    }

    /**
     * @Route("/user-registration-token", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN', 'ROLE_AD')")
     */
    public function getUserRegistrationToken(Request $request, KernelInterface $kernel, RestClient $restClient)
    {
        if ($kernel->getEnvironment() === 'prod') {
            throw $this->createNotFoundException();
        }

        $email = $request->query->get('email');

        $user = $restClient->get("user/get-one-by/email/$email", 'User');

        return new Response($user->getRegistrationToken());
    }
}

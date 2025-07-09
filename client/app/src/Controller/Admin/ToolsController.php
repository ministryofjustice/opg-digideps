<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\RestClientException;
use App\Form\Admin\Tool\ReportReassignmentType;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/tools")
 */
class ToolsController extends AbstractController
{
    public function __construct(
        private readonly RestClient $restClient,
    ) {
    }

    /**
     * @Route("/", name="admin_tools")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Tools/index.html.twig")
     */
    public function tools(): array
    {
        return [];
    }

    /**
     * @Route("/report-reassignment", name="admin_tools_report_reassignment")
     *
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     *
     * @Template("@App/Admin/Tools/report-reasssignment.html.twig")
     */
    public function reportAssignmentAction(Request $request): array
    {
        $form = $this->createForm(ReportReassignmentType::class, null, ['method' => 'POST']);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = $form->getData();

            try {
                $this->restClient->post('v2/tools/reassign-reports', json_encode([
                    'firstClientId' => $submitted['firstClientId'],
                    'secondClientId' => $submitted['secondClientId'],
                ]), [], 'response');
                $this->addFlash('fixture', 'Reports reassigned successfully!');
            } catch (RestClientException $e) {
                $this->addFlash('error', $e->getData()['message']);
            }
        }

        return ['form' => $form->createView()];
    }
}

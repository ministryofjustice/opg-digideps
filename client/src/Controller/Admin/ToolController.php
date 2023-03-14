<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/tools")
 */
class ToolController extends AbstractController
{
    public function __construct(
        private RestClient $restClient
    ) {
    }

    /**
     * @Route("/", name="admin_tools")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Tools/index.html.twig")
     */
    public function tools()
    {
        return [];
    }

    /**
     * @Route("/case-reassignment", name="admin_tools_case_reassignment")
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @Template("@App/Admin/Tools/case-asssignment.html.twig")
     */
    public function caseAssignmentAction(Request $request)
    {
    }
}

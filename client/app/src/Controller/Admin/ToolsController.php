<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Exception\RestClientException;
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
}

<?php

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/case")
 */
class CaseController extends AbstractController
{
    /** @var RestClient */
    private $restClient;

    public function __construct(
        RestClient $restClient
    ) {
        $this->restClient = $restClient;
    }

    /**
     * @Route("/{caseNumber}/details", name="admin_case_details")
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param string $caseNumber
     *
     * @return array
     */
    public function detailsAction($caseNumber)
    {
        return [];
    }
}

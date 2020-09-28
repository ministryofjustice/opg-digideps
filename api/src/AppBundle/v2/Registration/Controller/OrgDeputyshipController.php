<?php declare(strict_types=1);


namespace AppBundle\v2\Registration\Controller;

use AppBundle\v2\Controller\ControllerTrait;
use AppBundle\v2\Registration\Assembler\CasRecToOrgDeputyshipDtoAssembler;
use AppBundle\v2\Registration\Uploader\OrgDeputyshipUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrgDeputyshipController extends AbstractController
{
    use ControllerTrait;

    /** @var OrgDeputyshipUploader */
    private $orgDeputyshipUploader;

    /**  @var CasRecToOrgDeputyshipDtoAssembler */
    private $assembler;

    /**
     * OrgDeputyshipController constructor.
     * @param OrgDeputyshipUploader $orgDeputyshipUploader
     * @param CasRecToOrgDeputyshipDtoAssembler $assembler
     */
    public function __construct(
        OrgDeputyshipUploader $orgDeputyshipUploader,
        CasRecToOrgDeputyshipDtoAssembler $assembler
    ) {
        $this->orgDeputyshipUploader = $orgDeputyshipUploader;
        $this->assembler = $assembler;
    }

    /**
     * @Route("/org-deputyships", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function create(Request $request)
    {
        $uploadResults = ['errors' => 0];
        $added = ['clients' => [], 'discharged_clients' => [], 'named_deputies' => [], 'reports' => []];
        $uploadResults['added'] = $added;

        return new JsonResponse($uploadResults, Response::HTTP_CREATED);
    }
}

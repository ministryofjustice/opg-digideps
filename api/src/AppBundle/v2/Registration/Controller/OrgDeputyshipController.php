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
    private $uploader;

    /**  @var CasRecToOrgDeputyshipDtoAssembler */
    private $assembler;

    /**
     * OrgDeputyshipController constructor.
     * @param OrgDeputyshipUploader $orgDeputyshipUploader
     */
    public function __construct(
        OrgDeputyshipUploader $orgDeputyshipUploader,
        CasRecToOrgDeputyshipDtoAssembler $assembler
    ) {
        $this->uploader = $orgDeputyshipUploader;
        $this->assembler = $assembler;
    }

    /**
     * @Route("/org-deputyships", methods={"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function create(Request $request)
    {
        $decodedRows = json_decode($request->getContent(), true);
        $dtos = $this->assembler->assembleMultipleDtosFromArray($decodedRows);

        $uploadResults = $this->uploader->upload($dtos);

        return new JsonResponse($uploadResults, Response::HTTP_CREATED);
    }
}

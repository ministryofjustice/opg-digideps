<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Service\RestHandler\OrganisationRestHandler;
use AppBundle\v2\Assembler\OrganisationAssembler;
use AppBundle\v2\Transformer\OrganisationTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/organisation")
 */
class OrganisationController
{
    use ControllerTrait;

    /** @var OrganisationRestHandler */
    private $restHandler;

    /** @var OrganisationRepository */
    private $repository;

    /** @var OrganisationAssembler */
    private $assembler;

    /** @var OrganisationTransformer */
    private $transformer;

    /**
     * @param OrganisationRestHandler $restHandler
     * @param OrganisationRepository $repository
     * @param OrganisationAssembler $assembler
     * @param OrganisationTransformer $transformer
     */
    public function __construct(
        OrganisationRestHandler $restHandler,
        OrganisationRepository $repository,
        OrganisationAssembler $assembler,
        OrganisationTransformer $transformer
    ) {
        $this->restHandler = $restHandler;
        $this->repository = $repository;
        $this->assembler = $assembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/list")
     * @Method({"GET"})
     *
     * @return JsonResponse
     */
    public function getAllAction()
    {
        $data = $this->repository->findAllArray();

        $organisationDtos = [];
        foreach ($data as $organisationArray) {
            $organisationDtos[] = $this->assembler->assembleFromArray($organisationArray);
        }

        $transformedDtos = [];
        foreach ($organisationDtos as $organisationDto) {
            $transformedDtos[] = $this->transformer->transform($organisationDto);
        }

        return $this->buildSuccessResponse($transformedDtos);
    }

    public function getByIdAction($id)
    {

    }

    /**
     * @Route("")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $entity = $this->restHandler->create($data);

        return $this->buildSuccessResponse(['id' => $entity->getId()], 'Organisation created', 201);
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"PUT"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function updateAction($id)
    {
        return $this->buildSuccessResponse([], 'Organisation updated' . $id, 204);
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"DELETE"})
     *
     * @param $id
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        return $this->buildSuccessResponse([], 'Organisation deleted' . $id);
    }
}

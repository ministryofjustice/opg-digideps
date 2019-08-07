<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\Repository\OrganisationRepository;
use AppBundle\Service\RestHandler\OrganisationRestHandler;
use AppBundle\v2\Assembler\OrganisationAssembler;
use AppBundle\v2\Transformer\OrganisationTransformer;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    )
    {
        $this->restHandler = $restHandler;
        $this->repository = $repository;
        $this->assembler = $assembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/list")
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return JsonResponse
     */
    public function getAllAction()
    {
        $data = $this->repository->getAllArray();

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

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param $id
     * @return JsonResponse
     */
    public function getByIdAction($id)
    {
        if (null === ($data = $this->repository->findArrayById($id))) {
            throw new NotFoundHttpException(sprintf('Organisation id %s not found', $id));
        }

        $dto = $this->assembler->assembleFromArray($data);
        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }

    /**
     * @Route("")
     * @Method({"POST"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
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
     * @Security("has_role('ROLE_ADMIN')")
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
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction($id)
    {
        $deleted = $this->repository->deleteById($id);

        $message = $deleted ? 'Organisation deleted' : 'Organisation not found. Nothing deleted';

        return $this->buildSuccessResponse([], $message);
    }
}

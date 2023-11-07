<?php

namespace App\v2\Controller;

use App\Repository\UserRepository;
use App\v2\Assembler\DeputyAssembler;
use App\v2\Transformer\DeputyTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/deputy")
 */
class DeputyController
{
    use ControllerTrait;

    /** @var UserRepository */
    private $repository;

    /** @var DeputyAssembler */
    private $assembler;

    /** @var DeputyTransformer */
    private $transformer;

    public function __construct(UserRepository $repository, DeputyAssembler $assembler, DeputyTransformer $transformer)
    {
        $this->repository = $repository;
        $this->assembler = $assembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getByIdAction($id)
    {
        if (null === ($data = $this->repository->findUserArrayById($id))) {
            throw new NotFoundHttpException(sprintf('Deputy id %s not found', $id));
        }

        $dto = $this->assembler->assembleFromArray($data);
        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }
}

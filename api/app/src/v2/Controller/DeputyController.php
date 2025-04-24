<?php

namespace App\v2\Controller;

use App\Repository\UserRepository;
use App\v2\Assembler\UserAssembler;
use App\v2\Transformer\UserTransformer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/deputy')]
class DeputyController
{
    use ControllerTrait;

    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserAssembler $assembler,
        private readonly UserTransformer $transformer
    ) {
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
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

<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\Repository\UserRepository;
use AppBundle\v2\Assembler\DeputyAssembler;
use AppBundle\v2\Transformer\DeputyTransformer;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/deputy")
 */
class DeputyController
{
    use ControllerTrait;

    /** @var UserRepository  */
    private $repository;

    /** @var DeputyAssembler */
    private $assembler;

    /** @var DeputyTransformer */
    private $transformer;

    /**
     * @param UserRepository $repository
     * @param DeputyAssembler $assembler
     * @param DeputyTransformer $transformer
     */
    public function __construct(UserRepository $repository, DeputyAssembler $assembler, DeputyTransformer $transformer)
    {
        $this->repository = $repository;
        $this->assembler = $assembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @param $id
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

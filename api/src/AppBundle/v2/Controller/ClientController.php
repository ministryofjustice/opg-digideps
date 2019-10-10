<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\Repository\ClientRepository;
use AppBundle\v2\Assembler\ClientAssembler;
use AppBundle\v2\Transformer\ClientTransformer;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/client")
 */
class ClientController
{
    use ControllerTrait;

    /** @var ClientRepository  */
    private $repository;

    /** @var ClientAssembler */
    private $assembler;

    /** @var ClientTransformer */
    private $transformer;

    /**
     * @param ClientRepository $repository
     * @param ClientAssembler $assembler
     * @param ClientTransformer $transformer
     */
    public function __construct(ClientRepository $repository, ClientAssembler $assembler, ClientTransformer $transformer)
    {
        $this->repository = $repository;
        $this->assembler = $assembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_CASE_MANAGER')")
     *
     * @param $id
     * @return JsonResponse
     */
    public function getByIdAction($id)
    {
        if (null === ($data = $this->repository->getArrayById($id))) {
            throw new NotFoundHttpException(sprintf('Client id %s not found', $id));
        }

        $dto = $this->assembler->assembleFromArray($data);

        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }
}

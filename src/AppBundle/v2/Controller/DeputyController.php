<?php

namespace AppBundle\v2\Controller;

use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\Repository\DeputyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/deputy")
 */
class DeputyController
{
    /** @var DeputyRepository  */
    private $repository;

    /**
     * @param DeputyRepository $repository
     */
    public function __construct(DeputyRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param $id
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getByIdAction($id)
    {
        return $this->buildSuccessResponse($this->repository->getDtoById($id));
    }

    /**
     * @param DeputyDto $dto
     * @return JsonResponse
     */
    private function buildSuccessResponse(DeputyDto $dto)
    {
        return new JsonResponse([
            'success' => true,
            'data' => $dto->jsonSerialize(),
            'message' => ''
        ]);
    }
}

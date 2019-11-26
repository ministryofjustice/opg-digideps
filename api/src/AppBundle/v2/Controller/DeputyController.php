<?php

namespace AppBundle\v2\Controller;

use AppBundle\Entity\Repository\UserRepository;
use AppBundle\Entity\User;
use AppBundle\v2\Assembler\DeputyAssembler;
use AppBundle\v2\Assembler\LayDeputyAssemblerDecorator;
use AppBundle\v2\Assembler\OrgDeputyAssemblerDecorator;
use AppBundle\v2\DTO\DeputyDto;
use AppBundle\v2\Transformer\DeputyTransformer;
use AppBundle\v2\Transformer\LayDeputyTransformerDecorator;
use AppBundle\v2\Transformer\OrgDeputyTransformerDecorator;
use Doctrine\ORM\Query;
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

    /** @var LayDeputyAssemblerDecorator */
    private $layAssembler;

    /** @var OrgDeputyAssemblerDecorator */
    private $orgAssembler;

    /** @var LayDeputyTransformerDecorator */
    private $layTransformer;

    /** @var OrgDeputyTransformerDecorator */
    private $orgTransformer;

    /**
     * @param UserRepository $repository
     * @param LayDeputyAssemblerDecorator $layAssembler
     * @param OrgDeputyAssemblerDecorator $orgAssembler
     * @param LayDeputyTransformerDecorator $layTransformer
     * @param OrgDeputyTransformerDecorator $orgTransformer
     */
    public function __construct(
        UserRepository $repository,
        LayDeputyAssemblerDecorator $layAssembler,
        OrgDeputyAssemblerDecorator $orgAssembler,
        LayDeputyTransformerDecorator $layTransformer,
        OrgDeputyTransformerDecorator $orgTransformer
    ) {
        $this->repository = $repository;
        $this->layAssembler = $layAssembler;
        $this->orgAssembler = $orgAssembler;
        $this->layTransformer = $layTransformer;
        $this->orgTransformer = $orgTransformer;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getByIdAction(int $id): JsonResponse
    {
        if (null === ($roleOfRequestedUser = $this->repository->getColumnById('roleName', $id))) {
            throw new NotFoundHttpException(sprintf('Deputy id %s not found', $id));
        }

        $transformedDto = User::ROLE_LAY_DEPUTY === $roleOfRequestedUser
            ? $this->buildTransformedLayUserData($id)
            : $this->buildTransformedOrgUserData($id);

        return $this->buildSuccessResponse($transformedDto);
    }

    /**
     * @param int $id
     * @return array
     */
    private function buildTransformedLayUserData(int $id): array
    {
        $data = $this->repository->findLayUserArrayById($id);
        $dto = $this->layAssembler->assembleFromArray($data);

        return $this->layTransformer->transform($dto);
    }

    /**
     * @param int $id
     * @return array
     */
    private function buildTransformedOrgUserData(int $id): array
    {
        $data = $this->repository->findOrgUserArrayById($id);
        $dto = $this->orgAssembler->assembleFromArray($data);

        return $this->orgTransformer->transform($dto);
    }
}

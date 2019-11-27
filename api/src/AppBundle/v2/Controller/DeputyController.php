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
    private $transformer;

    /**
     * @param UserRepository $repository
     * @param LayDeputyAssemblerDecorator $layAssembler
     * @param OrgDeputyAssemblerDecorator $orgAssembler
     * @param DeputyTransformer $transformer
     */
    public function __construct(
        UserRepository $repository,
        LayDeputyAssemblerDecorator $layAssembler,
        OrgDeputyAssemblerDecorator $orgAssembler,
        DeputyTransformer $transformer
    ) {
        $this->repository = $repository;
        $this->layAssembler = $layAssembler;
        $this->orgAssembler = $orgAssembler;
        $this->transformer = $transformer;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getByIdAction(int $id): JsonResponse
    {
        /** @var array $user */
        if (null === ($user = $this->repository->findUserArrayById($id))) {
            throw new NotFoundHttpException(sprintf('Deputy id %s not found', $id));
        }

        /** @var DeputyDto $dto */
        $dto = $this->determineAssembler($user)->assembleFromArray($user);

        /** @var array $transformedDto */
        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }

    /**
     * @param array $user
     * @return LayDeputyAssemblerDecorator|OrgDeputyAssemblerDecorator
     */
    private function determineAssembler(array $user)
    {
        return ($user['roleName'] === User::ROLE_LAY_DEPUTY) ? $this->layAssembler : $this->orgAssembler;
    }
}

<?php

namespace App\v2\Controller;

use App\Controller\RestController;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\OrganisationAssembler;
use App\v2\Transformer\ClientTransformer;
use App\v2\Transformer\OrganisationTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/client')]
class ClientController extends RestController
{
    use ControllerTrait;

    public function __construct(
        private readonly ClientRepository $repository,
        private readonly ClientAssembler $clientAssembler,
        private readonly OrganisationAssembler $orgAssembler,
        private readonly ClientTransformer $clientTransformer,
        private readonly OrganisationTransformer $orgTransformer,
        EntityManagerInterface $em
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD') or is_granted('ROLE_DEPUTY')")]
    public function getByIdAction(int $id): JsonResponse
    {
        if (null === ($data = $this->repository->getArrayById($id))) {
            throw new NotFoundHttpException(sprintf('Client id %s not found', $id));
        }

        $dto = $this->clientAssembler->assembleFromArray($data);

        $orgDto = null;
        $transformedOrg = null;

        if (isset($data['organisation'])) {
            $orgDto = $this->orgAssembler->assembleFromArray($data['organisation']);
            $transformedOrg = $this->orgTransformer->transform($orgDto, ['total_user_count', 'total_client_count', 'users', 'clients']);
        }

        $transformedDto = $this->clientTransformer->transform($dto, [], $transformedOrg);

        /* @var Client $client */
        $client = $this->findEntityBy(Client::class, $transformedDto['id']);

        if (!$this->isGranted('view', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        return $this->buildSuccessResponse($transformedDto);
    }

    #[Route(path: '/case-number/{caseNumber}', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD') or is_granted('ROLE_DEPUTY')")]
    public function getByCaseNumber(string $caseNumber): JsonResponse
    {
        if (null === ($data = $this->repository->getArrayByCaseNumber($caseNumber))) {
            throw new NotFoundHttpException(sprintf('Client with case number %s not found', $caseNumber));
        }

        $dto = $this->clientAssembler->assembleFromArray($data);

        $transformedDto = $this->clientTransformer->transform($dto, ['reports', 'ndr', 'organisation', 'deputy']);

        if ($transformedDto['archived_at']) {
            throw $this->createAccessDeniedException('Cannot access archived reports');
        }

        /* @var $client Client */
        $client = $this->findEntityBy(Client::class, $transformedDto['id']);

        if (!$this->isGranted('view', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        return $this->buildSuccessResponse($transformedDto);
    }
}

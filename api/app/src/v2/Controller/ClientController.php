<?php

namespace App\v2\Controller;

use App\Controller\RestController;
use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Assembler\OrganisationAssembler;
use App\v2\Transformer\ClientTransformer;
use App\v2\Transformer\OrganisationTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/client")
 */
class ClientController extends RestController
{
    use ControllerTrait;

    /** @var ClientRepository */
    private $repository;

    /** @var ClientAssembler */
    private $clientAssembler;

    /** @var OrganisationAssembler */
    private $orgAssembler;

    /** @var ClientTransformer */
    private $clientTransformer;

    /** @var OrganisationTransformer */
    private $orgTransformer;

    public function __construct(
        ClientRepository $repository,
        ClientAssembler $clientAssembler,
        OrganisationAssembler $orgAssembler,
        ClientTransformer $clientTransformer,
        OrganisationTransformer $orgTransformer
    ) {
        $this->repository = $repository;
        $this->clientAssembler = $clientAssembler;
        $this->orgAssembler = $orgAssembler;
        $this->clientTransformer = $clientTransformer;
        $this->orgTransformer = $orgTransformer;
    }

    /**
     * @Route("/{id}", requirements={"id":"\d+"}, methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD') or is_granted('ROLE_DEPUTY')")
     */
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

    /**
     * @Route("/case-number/{caseNumber}", methods={"GET"})
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD') or is_granted('ROLE_DEPUTY')")
     */
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

<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\v2\Controller;

use Doctrine\ORM\EntityManagerInterface;
use OPG\Digideps\Backend\Controller\RestController;
use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Repository\ClientRepository;
use OPG\Digideps\Backend\v2\Assembler\ClientAssembler;
use OPG\Digideps\Backend\v2\Assembler\OrganisationAssembler;
use OPG\Digideps\Backend\v2\Transformer\ClientTransformer;
use OPG\Digideps\Backend\v2\Transformer\OrganisationTransformer;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_DEPUTY')"))]
    public function getById(int $id): JsonResponse
    {
        $client = $this->repository->getEntityById($id);

        if ($client === null) {
            throw new NotFoundHttpException("Client id {$id} not found");
        }

        if (!$this->isGranted('view', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        $dto = $this->clientAssembler->assembleFromEntity($client);

        // note that we have to do assemble/transform separately for the organisation
        // to avoid circular references
        $transformedOrg = null;
        $organisation = $client->getOrganisation();
        if ($organisation !== null) {
            $orgDto = $this->orgAssembler->assembleFromArray([
                'id' => $organisation->getId(),
                'name' => $organisation->getName(),
                'emailIdentifier' => $organisation->getEmailIdentifier(),
                'isActivated' => $organisation->isActivated(),
            ]);
            $transformedOrg = $this->orgTransformer->transform($orgDto, ['total_user_count', 'total_client_count', 'users', 'clients']);
        }

        $transformedDto = $this->clientTransformer->transform($dto, [], $transformedOrg);

        return $this->buildSuccessResponse($transformedDto);
    }

    #[Route(path: '/case-number/{caseNumber}', methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_DEPUTY')"))]
    public function getByCaseNumber(string $caseNumber): JsonResponse
    {
        if (null === ($data = $this->repository->getArrayByCaseNumber($caseNumber))) {
            throw new NotFoundHttpException(sprintf('Client with case number %s not found', $caseNumber));
        }

        $dto = $this->clientAssembler->assembleFromArray($data);

        $transformedDto = $this->clientTransformer->transform($dto, ['reports', 'organisation', 'deputy']);

        if ($transformedDto['archived_at']) {
            throw $this->createAccessDeniedException('Cannot access archived reports');
        }

        /** @var int $clientId */
        $clientId = $transformedDto['id'] ?? 0;

        /* @var ?Client $client */
        $client = $this->findEntityBy(Client::class, $clientId);

        if (!$this->isGranted('view', $client)) {
            if (!$this->checkIfUserHasAccessViaDeputyUid($client->getId())) {
                throw $this->createAccessDeniedException('Client does not belong to user');
            }
        }

        return $this->buildSuccessResponse($transformedDto);
    }
}

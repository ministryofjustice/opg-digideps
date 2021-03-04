<?php

namespace App\v2\Controller;

use App\Entity\Client;
use App\Controller\RestController;
use App\Repository\ClientRepository;
use App\v2\Assembler\ClientAssembler;
use App\v2\Transformer\ClientTransformer;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/client")
 */
class ClientController extends RestController
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
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_DEPUTY')")
     *
     * @param $id
     * @return JsonResponse
     */
    public function getByIdAction(int $id): JsonResponse
    {
        if (null === ($data = $this->repository->getArrayById($id))) {
            throw new NotFoundHttpException(sprintf('Client id %s not found', $id));
        }

        $dto = $this->assembler->assembleFromArray($data);

        $transformedDto = $this->transformer->transform($dto);

        if ($transformedDto['archived_at']) {
            throw $this->createAccessDeniedException('Cannot access archived reports');
        };

        /* @var $client Client */
        $client = $this->findEntityBy(Client::class, $transformedDto['id']);

        if (!$this->isGranted('view', $client)) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        return $this->buildSuccessResponse($transformedDto);
    }

    /**
     * @Route("/case-number/{caseNumber}", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD') or has_role('ROLE_DEPUTY')")
     *
     * @param string $caseNumber
     * @return JsonResponse
     */
    public function getByCaseNumber(string $caseNumber): JsonResponse
    {
        if (null === ($data = $this->repository->getArrayByCaseNumber($caseNumber))) {
            throw new NotFoundHttpException(sprintf('Client with case number %s not found', $caseNumber));
        }

        $dto = $this->assembler->assembleFromArray($data);

        $transformedDto = $this->transformer->transform($dto, ['reports', 'ndr', 'organisation', 'namedDeputy']);

        if ($transformedDto['archived_at']) {
            throw $this->createAccessDeniedException('Cannot access archived reports');
        };

        /* @var $client Client */
        $client = $this->findEntityBy(Client::class, $transformedDto['id']);

        if (!$this->isGranted('view', $client)) {
            throw $this->createAccessDeniedException('Client does not belong to user');
        }

        return $this->buildSuccessResponse($transformedDto);
    }
}

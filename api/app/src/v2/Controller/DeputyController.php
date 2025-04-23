<?php

namespace App\v2\Controller;

use App\Repository\DeputyRepository;
use App\Repository\UserRepository;
use App\v2\Assembler\UserAssembler;
use App\v2\Transformer\UserTransformer;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/deputy', name: 'v2_deputy_')]
class DeputyController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private readonly DeputyRepository $deputyRepository,
        private readonly LoggerInterface $logger,
        private readonly UserRepository $repository,
        private readonly UserAssembler $assembler,
        private readonly UserTransformer $transformer,
    ) {
    }

    #[Route(path: '/{id}', requirements:['id' => '\d+'], methods: ['GET'])]
    public function getByIdAction($id): JsonResponse
    {
        if (null === ($data = $this->repository->findUserArrayById($id))) {
            $this->buildNotFoundResponse(sprintf('Deputy id %s not found', $id));
        }

        $dto = $this->assembler->assembleFromArray($data);
        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }

    #[Route(path:'/{uid}/reports', name:'deputy_find_reports_by_uid', requirements:['uid' => '\d+'], methods:['GET'])]
    public function getAllDeputyReports(Request $request, int $uid): JsonResponse
    {
        $user = $this->getUser();

        if ($uid !== $user->getDeputyUid()) {
            return $this->buildNotFoundResponse('Deputy uid provided does not match current logged in user');
        }

        $inactive = $request->query->has('inactive') ? $request->query->get('inactive') : null;

        try {
            $results = $this->deputyRepository->findReportsInfoByUid($uid, (bool) $inactive);
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error occurred during Deputy report retrieval:%s', $e->getMessage()));

            return $this->buildErrorResponse();
        }
        
        if (is_null($results)) {
            return $this->buildNotFoundResponse();
        }

        return $this->buildSuccessResponse($results);
    }
}

<?php

namespace App\v2\Controller;

use App\Entity\Organisation;
use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\OrganisationRepository;
use App\Repository\UserRepository;
use App\Service\Formatter\RestFormatter;
use App\Service\RestHandler\OrganisationRestHandler;
use App\v2\Assembler\OrganisationAssembler;
use App\v2\Transformer\OrganisationTransformer;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/organisation')]
class OrganisationController extends AbstractController
{
    use ControllerTrait;

    public function __construct(
        private OrganisationRestHandler $restHandler,
        private OrganisationRepository $organisationRepository,
        private UserRepository $userRepository,
        private ClientRepository $clientRepository,
        private OrganisationAssembler $assembler,
        private OrganisationTransformer $transformer,
        private RestFormatter $formatter,
        private LoggerInterface $verboseLogger
    ) {
    }

    private static array $jmsGroups = [
        'client-id',
        'client-name',
        'client-case-number',
        'total-report-count',
        'user-list',
    ];

    #[Route(path: '/list', methods: ['GET'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function getAllAction(): JsonResponse
    {
        // Fetch all data from db
        $data = $this->organisationRepository->getNonDeletedArray();

        $data = $this->snakeCase($data);

        // Pass transformed org data to repsonse
        return $this->buildSuccessResponse($data);
    }

    private function snakeCase(array $array): array
    {
        return array_map(
            function ($item) {
                if (is_array($item)) {
                    $item = $this->snakeCase($item);
                }

                return $item;
            },
            $this->doSnakeCase($array)
        );
    }

    private function doSnakeCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $key = strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $key));

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * @Entity("organisation", expr="repository.find(id)")
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('view', organisation)")]
    public function getByIdAction(Organisation $organisation): JsonResponse
    {
        $dto = $this->assembler->assembleFromEntity($organisation);
        $transformedDto = $this->transformer->transform($dto);

        return $this->buildSuccessResponse($transformedDto);
    }

    /**
     * @Entity("organisation", expr="repository.find(id)")
     */
    #[Route(path: '/{id}/users', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('view', organisation)")]
    public function getUsers(Organisation $organisation, Request $request)
    {
        $ret = $this->userRepository->findByFiltersWithCounts(
            $request->get('q'),
            $request->get('offset', 0),
            $request->get('limit', 15),
            $organisation->getId()
        );

        $this->formatter->setJmsSerialiserGroups(self::$jmsGroups);

        return $ret;
    }

    /**
     * @Entity("organisation", expr="repository.find(id)")
     */
    #[Route(path: '/{id}/clients', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('view', organisation)")]
    public function getClients(Organisation $organisation, Request $request)
    {
        $ret = $this->clientRepository->findByFiltersWithCounts(
            $request->get('q'),
            $request->get('offset', 0),
            $request->get('limit', 15),
            $organisation->getId()
        );

        $this->formatter->setJmsSerialiserGroups(self::$jmsGroups);

        return $ret;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '', methods: ['POST'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function createAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $entity = $this->restHandler->create($data);

        return $this->buildSuccessResponse(['id' => $entity->getId()], 'Organisation created', Response::HTTP_CREATED);
    }

    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['PUT'])]
    #[Security("is_granted('ROLE_ADMIN')")]
    public function updateAction(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->restHandler->update($data, $id);

        return $this->buildSuccessResponse([], '', Response::HTTP_NO_CONTENT);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/{id}', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[Security("is_granted('ROLE_SUPER_ADMIN')")]
    public function deleteAction(int $id): JsonResponse
    {
        $deleted = $this->restHandler->delete($id);
        $message = $deleted ? 'Organisation deleted' : 'Organisation not found. Nothing deleted';

        return $this->buildSuccessResponse([], $message);
    }

    /**
     * @Entity("organisation", expr="repository.find(orgId)")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/{orgId}/user/{userId}', requirements: ['orgId' => '\d+', 'userId' => '\d+'], methods: ['PUT'])]
    #[Security("is_granted('edit', organisation)")]
    public function addUserAction(Request $request, Organisation $organisation, int $userId): JsonResponse
    {
        $orgId = $organisation->getId();
        $this->restHandler->addUser($orgId, $userId);

        return $this->buildSuccessResponse([], 'User added', Response::HTTP_NO_CONTENT);
    }

    /**
     * @Entity("organisation", expr="repository.find(orgId)")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/{orgId}/user/{userId}', requirements: ['orgId' => '\d+', 'userId' => '\d+'], methods: ['DELETE'])]
    #[Security("is_granted('edit', organisation)")]
    public function removeUserAction(Organisation $organisation, int $userId): JsonResponse
    {
        $orgId = $organisation->getId();
        $this->restHandler->removeUser($orgId, $userId);

        return $this->buildSuccessResponse([], 'User removed');
    }

    #[Route(path: '/members', methods: ['GET'])]
    #[Security("is_granted('ROLE_ORG')")]
    public function getMembers(Request $request)
    {
        return $this->getUser()->getOrganisations()[0]->getUsers();
    }

    #[Route(path: '/member/{id}', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[Security("is_granted('ROLE_ORG')")]
    public function getMemberById(string $id)
    {
        return $this->getUser()->getOrganisations()[0]->getUsers()->get($id);
    }
}

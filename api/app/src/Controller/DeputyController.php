<?php

namespace App\Controller;

use App\Entity\Deputy;
use App\Entity\User;
use App\Service\DeputyService;
use App\Service\Formatter\RestFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/deputy')]
class DeputyController extends RestController
{
    public function __construct(
        private readonly DeputyService $deputyService,
        private readonly RestFormatter $formatter,
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct($em);
    }

    #[Route(path: '/add', methods: ['POST'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')"))]
    public function add(Request $request): array
    {
        $data = $this->formatter->deserializeBodyContent($request);
        $newDeputy = $this->deputyService->populateDeputy($data);

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $deputy = $this->deputyService->getOrAddDeputy($newDeputy, $currentUser);

        return ['id' => $deputy->getId()];
    }

    #[Route(path: '/{id}', name: 'deputy_find_by_id', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_DEPUTY') or is_granted('ROLE_ADMIN')"))]
    public function findById(Request $request, int $id): Deputy
    {
        $serialisedGroups = $request->query->has('groups')
            ? $request->query->all('groups') : ['deputy'];
        $this->formatter->setJmsSerialiserGroups($serialisedGroups);

        return $this->findEntityBy(Deputy::class, $id);
    }
}

<?php declare(strict_types=1);


namespace App\Controller;

use App\Entity\Repository\UserResearchResponseRepository;
use App\Factory\UserResearchResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserResearchController extends RestController
{
    private UserResearchResponseFactory $factory;
    private UserResearchResponseRepository $repository;

    public function __construct(UserResearchResponseFactory $factory, UserResearchResponseRepository $repository)
    {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    /**
     * @Route("/user-research", name="user_research_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        try {
            $formData = json_decode($request->getContent(), true);

            $userResearchResponse = $this->factory->generateFromFormData($formData);
            $this->repository->create($userResearchResponse);

            return 'Created';
        } catch (\Throwable $e) {
            return sprintf('UserResearchResponse not created: %s', $e->getMessage());
        }

//        deputyshipLength => 'underOne',
//        agreedResearchTypes => ['surveys', 'videoCall', 'phone', 'inPerson']
//        hasAccessToVideoCallDevice = yes
    }
}

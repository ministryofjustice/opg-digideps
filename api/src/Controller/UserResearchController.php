<?php declare(strict_types=1);


namespace App\Controller;

use App\Repository\UserResearchResponseRepository;
use App\Factory\UserResearchResponseFactory;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Security("has_role('ROLE_DEPUTY') or has_role('ROLE_ORG')")
     */
    public function create(Request $request)
    {
        try {
            $formData = json_decode($request->getContent(), true);

            $userResearchResponse = $this->factory->generateFromFormData($formData);
            $this->repository->create($userResearchResponse, $this->getUser());

            return 'Created';
        } catch (\Throwable $e) {
            throw new RuntimeException(sprintf('UserResearchResponse not created: %s', $e->getMessage()), Response::HTTP_BAD_REQUEST);
        }
    }
}

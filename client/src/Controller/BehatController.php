<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\Client\Internal\UserApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    private UserApi $userApi;
    private string $symfonyEnvironment;

    public function __construct(
        UserApi $userApi,
        string $symfonyEnvironment
    ) {
        $this->userApi = $userApi;
        $this->symfonyEnvironment = $symfonyEnvironment;
    }

    /**
     * @Route("/behat/frontend/user/{email}/details", name="behat_front_get_user_details_by_email", methods={"GET"})
     *
     * @param string $email
     * @return JsonResponse
     */
    public function getUserDetails(string $email)
    {
        if ($this->symfonyEnvironment === 'prod') {
            throw $this->createNotFoundException();
        }

        $user = $this->userApi->getByEmail($email, ['user-login', 'user-id']);

        return new JsonResponse(
            [
                'UserId' => $user->getId(),
                'ActiveReportId' => $user->getActiveReportId()
            ]
        );
    }
}

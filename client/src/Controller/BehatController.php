<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\TokenStorageInterface;
use App\Service\DeputyProvider;
use GuzzleHttp\ClientInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BehatController extends AbstractController
{
    private RestClient $restClient;
    private TokenStorageInterface $tokenStorage;
    private DeputyProvider $deputyProvider;
    private ClientInterface $client;

    public function __construct(
        RestClient $restClient,
        TokenStorageInterface $tokenStorage,
        DeputyProvider $deputyProvider,
        ClientInterface $client
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
        $this->deputyProvider = $deputyProvider;
        $this->client = $client;
    }

    /**
     * @Route("/front/behat/auth-as", name="behat_front_auth_as", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param string $orgName
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authAs(Request $request)
    {
        $email = $request->query->get('email');
        $creds = ['email' => $email, 'password' => 'Abcd1234'];

        $response = $this->client->request(
            'POST',
            '/auth/login',
            [
                'json' => $creds,
                'headers' => ['ClientSecret' => 'api-frontend-key']
            ]
        );

        $token = $response->getHeader('AuthToken')[0];
        $data = json_decode($response->getBody()->getContents(), true)['data'];
        $this->tokenStorage->set($data['id'], $token);

//        $this->tokenStorage->set($user->getId(), $tokenVal);
        return new JsonResponse(['AuthToken' => $token, 'UserId' => $data['id'], 'ActiveReportId' => $data['active_report_id']]);
    }
}

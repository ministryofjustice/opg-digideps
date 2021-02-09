<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\Client\RestClient;
use App\Service\Client\TokenStorage\TokenStorageInterface;
use App\Service\DeputyProvider;
use GuzzleHttp\ClientInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BehatController extends AbstractController
{
    private RestClient $restClient;
    private TokenStorageInterface $tokenStorage;
    private DeputyProvider $deputyProvider;
    private ClientInterface $client;
    private SessionInterface $session;

    public function __construct(
        RestClient $restClient,
        TokenStorageInterface $tokenStorage,
        DeputyProvider $deputyProvider,
        ClientInterface $client,
        SessionInterface $session
    ) {
        $this->restClient = $restClient;
        $this->tokenStorage = $tokenStorage;
        $this->deputyProvider = $deputyProvider;
        $this->client = $client;
        $this->session = $session;
    }

    /**
     * @Route("/front/behat/auth-as", name="behat_front_auth_as", methods={"GET"})
     *
     * @param string $orgName
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authAs(Request $request)
    {
//        $firewallName = 'secured_area';
//        $firewallContext = 'secured_area';
//        $email = $request->query->get('email');
//
//        $token = new UsernamePasswordToken($email, 'Abcd1234', $firewallName, ['ROLE_PROF_ADMIN']);
//
//        $this->session->set('_security_'.$firewallContext, serialize($token));
//        $this->session->save();
//
//        $cookie = new Cookie($this->session->getName(), $this->session->getId());
//
//        $response = new JsonResponse(['AuthToken' => $token->getProviderKey()]);
//        $response->headers->setCookie($cookie);
//        return $response;

        $email = $request->query->get('email');
        $creds = ['email' => strtolower($email), 'password' => 'Abcd1234'];

        $user = $this->restClient->login($creds);

        $authToken = $this->tokenStorage->get($user->getId());

        $this->restClient->setLoggedUserId($user->getId());

        // set logged user ID to the restClient (for future requests in this lifespan. e.g. set password on user activation)
//        $this->restClient->setLoggedUserId($user->getId());
//
//        $response = $this->client->request(
//            'POST',
//            '/auth/login',
//            [
//                'json' => $creds,
//                'headers' => ['ClientSecret' => 'api-frontend-key']
//            ]
//        );
//
//        $token = $response->getHeader('AuthToken')[0];
//        $data = json_decode($response->getBody()->getContents(), true)['data'];
//
//        $this->tokenStorage->set($data['id'], $token);
//
//        $firewallName = 'secured_area';
//        $firewallContext = 'secured_area';
//        $usernameToken = new UsernamePasswordToken($email, 'Abcd1234', $firewallName, ['ROLE_PROF_ADMIN']);
//
//        $this->session->set('_security_'.$firewallContext, serialize($usernameToken));
//        $this->session->save();

//        return new JsonResponse(['AuthToken' => $token, 'UserId' => $data['id'], 'ActiveReportId' => $data['active_report_id'], 'data' => $data]);
        return new JsonResponse(['UserId' => $user->getId(), 'ActiveReportId' => $user->getActiveReportId(), 'AuthToken' => $authToken]);
    }
}

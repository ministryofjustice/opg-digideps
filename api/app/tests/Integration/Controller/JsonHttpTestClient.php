<?php

namespace App\Tests\Integration\Controller;

use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotEmpty;

class JsonHttpTestClient
{
    private KernelBrowser $client;

    public function __construct(KernelBrowser $client)
    {
        $this->client = $client;
    }

    /**
     * @param array $options with keys method, uri, data, mustSucceed, mustFail, assertId
     */
    public function assertJsonRequest($method, $uri, array $options = []): array
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if (isset($options['AuthToken'])) {
            $headers['HTTP_AuthToken'] = $options['AuthToken'];
        }
        if (isset($options['ClientSecret'])) {
            $headers['HTTP_ClientSecret'] = $options['ClientSecret'];
        }

        $rawData = null;
        if (isset($options['data'])) {
            $rawData = json_encode($options['data']);
        } elseif (isset($options['rawData'])) {
            $rawData = $options['rawData'];
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            $rawData
        );

        /** @var Response $response */
        $response = $this->client->getResponse();
        assertEquals(
            $response->headers->contains('Content-Type', 'application/json'),
            'wrong content type. Headers: '.$headers['CONTENT_TYPE']
        );

        /** @var string $content */
        $content = $response->getContent();
        $return = json_decode($content, true);

        assertNotEmpty($return, 'Response not json');

        if (!empty($options['mustSucceed'])) {
            assert($return['success'], "Endpoint didn't succeed as expected. Response: ".print_r($return, true));
            if (!empty($options['assertId'])) {
                assert($return['data']['id'] > 0);
            }
        }

        if (!empty($options['mustFail'])) {
            assertFalse($return['success'], "Endpoint didn't fail as expected. Response: ".print_r($return, true));
        }

        if (!empty($options['assertCode'])) {
            assertEquals($options['assertResponseCode'], $return['code'], 'Response: '.print_r($return, true));
        }

        if (!empty($options['assertResponseCode'])) {
            assertEquals($options['assertResponseCode'], $response->getStatusCode(), 'Response: '.$response->getStatusCode().print_r($return, true));
        }

        return $return;
    }

    /**
     * @param string|false $clientSecret
     *
     * @return mixed token
     *
     * @throws \Exception
     */
    public function login(string $email, string $password, $clientSecret)
    {
        $this->client->request('GET', '/'); // warm up to get container

        // reset brute-force counters
        $key = 'email'.$email;

        /** @var Container $container */
        $container = $this->client->getContainer();

        /** @var AttemptsInTimeChecker $timeChecker */
        $timeChecker = $container->get(AttemptsInTimeChecker::class);
        $timeChecker->resetAttempts($key);

        /** @var AttemptsIncrementalWaitingChecker $waitingChecker */
        $waitingChecker = $container->get(AttemptsIncrementalWaitingChecker::class);
        $waitingChecker->resetAttempts($key);

        $responseArray = $this->assertJsonRequest('POST', '/auth/login', [
            'mustSucceed' => true,
            'ClientSecret' => $clientSecret,
            'data' => [
                'email' => $email,
                'password' => $password,
            ],
        ])['data'];

        assertEquals($email, $responseArray['email']);

        /** @var Response $response */
        $response = $this->client->getResponse();

        return $response->headers->get('AuthToken');
    }

    public function assertEndpointNeedsAuth($method, $uri, $authToken = 'WRONG')
    {
        $response = $this->assertJsonRequest($method, $uri, [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertResponseCode' => 419,
        ]);
        assertEquals(419, $response['code']);
    }
}

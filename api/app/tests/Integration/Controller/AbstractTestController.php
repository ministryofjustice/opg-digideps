<?php

namespace App\Tests\Integration\Controller;

use ReflectionObject;
use Exception;
use App\Service\BruteForce\AttemptsIncrementalWaitingChecker;
use App\Service\BruteForce\AttemptsInTimeChecker;
use App\Service\JWT\JWTService;
use App\Tests\Integration\Fixtures;
use Doctrine\ORM\EntityManager;
use Osteel\OpenApi\Testing\ValidatorBuilder;
use Osteel\OpenApi\Testing\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractTestController extends WebTestCase
{
    protected static EntityManager $em;
    protected static Fixtures $fixtures;
    protected static KernelBrowser $frameworkBundleClient;
    protected static string|false $deputySecret;
    protected static string|false $adminSecret;
    protected static ?JWTService $jwtService;
    protected ?int $loggedInUserId = null;
    private ?ValidatorInterface $openapiValidator = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::setupFixtures();
    }

    public static function setupFixtures(): void
    {
        // each test restores the db before launching the entire suite,
        // help to cleanup records created from previously-executed tests
        Fixtures::deleteReportsData();

        self::$frameworkBundleClient = static::createClient(['environment' => 'test', 'debug' => false]);

        self::$em = static::getContainer()->get('em');
        self::$fixtures = new Fixtures(self::$em);
        self::$em->clear();

        self::$jwtService = static::getContainer()->get(JWTService::class);
        self::$deputySecret = getenv('SECRETS_FRONT_KEY');
        self::$adminSecret = getenv('SECRETS_ADMIN_KEY');
    }

    public static function fixtures(): Fixtures
    {
        return self::$fixtures;
    }

    /**
     * @param array $options with keys method, uri, data, mustSucceed, mustFail, assertId
     */
    public function assertJsonRequest($method, $uri, array $options = [], bool $withValidJwt = false): array
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if (isset($options['AuthToken'])) {
            $headers['HTTP_AuthToken'] = $options['AuthToken'];
        }
        if (isset($options['ClientSecret'])) {
            $headers['HTTP_ClientSecret'] = $options['ClientSecret'];
        }

        if ($withValidJwt) {
            $headers['HTTP_JWT'] = self::$jwtService->createNewJWT();
        }

        $rawData = null;
        if (isset($options['data'])) {
            $rawData = json_encode($options['data']);
        } elseif (isset($options['rawData'])) {
            $rawData = $options['rawData'];
        }

        self::$frameworkBundleClient->request(
            $method,
            $uri,
            [],
            [],
            $headers,
            $rawData
        );

        $response = self::$frameworkBundleClient->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'), 'wrong content type. Headers: '.$headers['CONTENT_TYPE']);

        /** @var string $content */
        $content = $response->getContent();
        $return = json_decode($content, true);
        $this->assertNotEmpty($return, 'Response not json');
        if (!empty($options['mustSucceed'])) {
            $this->assertTrue($return['success'], "Endpoint didn't succeed as expected. Response: ".print_r($return, true));
            if (!empty($options['assertId'])) {
                $this->assertTrue($return['data']['id'] > 0);
            }
        }
        if (!empty($options['mustFail'])) {
            $this->assertFalse($return['success'], "Endpoint didn't fail as expected. Response: ".print_r($return, true));
        }
        if (!empty($options['assertCode'])) {
            $this->assertEquals($options['assertResponseCode'], $return['code'], 'Response: '.print_r($return, true));
        }
        if (!empty($options['assertResponseCode'])) {
            $this->assertEquals($options['assertResponseCode'], $response->getStatusCode(), 'Response: '.$response->getStatusCode().print_r($return, true));
        }

        return $return;
    }

    /**
     * @param string|false $clientSecret
     *
     * @return mixed token
     *
     * @throws Exception
     */
    public function login(string $email, string $password, $clientSecret)
    {
        self::$frameworkBundleClient->request('GET', '/'); // warm up to get container

        // reset brute-force counters
        $key = 'email'.$email;

        /** @var Container $container */
        $container = self::$frameworkBundleClient->getContainer();

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
        $this->assertEquals($email, $responseArray['email']);
        $this->loggedInUserId = $responseArray['id'];

        $response = self::$frameworkBundleClient->getResponse();

        return $response->headers->get('AuthToken');
    }

    protected function assertEndpointNeedsAuth($method, $uri, $authToken = 'WRONG')
    {
        $response = $this->assertJsonRequest($method, $uri, [
            'mustFail' => true,
            'AuthToken' => $authToken,
            'assertResponseCode' => 419,
        ]);
        $this->assertEquals(419, $response['code']);
    }

    protected function assertEndpointNotAllowedFor($method, $uri, $token, $data = [])
    {
        $this->assertJsonRequest($method, $uri, [
            'mustFail' => true,
            'data' => $data,
            'AuthToken' => $token,
            'assertResponseCode' => 403,
        ]);
    }

    protected function assertEndpointNotFoundFor($method, $uri, $token, $data = [])
    {
        $this->assertJsonRequest($method, $uri, [
            'mustFail' => true,
            'data' => $data,
            'AuthToken' => $token,
            'assertResponseCode' => 404,
        ]);
    }

    protected function assertEndpointAllowedFor($method, $uri, $token, $data = [])
    {
        $this->assertJsonRequest($method, $uri, [
            'mustSucceed' => true,
            'data' => $data,
            'AuthToken' => $token,
            'assertResponseCode' => 200,
        ]);
    }

    /**
     * @return string token
     */
    protected function loginAsDeputy(string $email = 'deputy@example.org')
    {
        return $this->login($email, 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsMultiClientPrimaryDeputy()
    {
        return $this->login('multi-client-primary-deputy@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsMultiClientNonPrimaryDeputy()
    {
        return $this->login('multi-client-non-primary-deputy@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsMainDeputy()
    {
        return $this->login('main-deputy@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsCoDeputy()
    {
        return $this->login('co-deputy@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsPa()
    {
        return $this->login('pa@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsPaAdmin()
    {
        return $this->login('pa_admin@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsPaTeamMember()
    {
        return $this->login('pa_team_member@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsProf()
    {
        return $this->login('prof@example.org', 'DigidepsPass1234', self::$deputySecret);
    }

    /**
     * @return string token
     */
    protected function loginAsAdmin()
    {
        return $this->login('admin@example.org', 'DigidepsPass1234', self::$adminSecret);
    }

    /**
     * @return string token
     */
    protected function loginAsSuperAdmin()
    {
        return $this->login('super_admin@example.org', 'DigidepsPass1234', self::$adminSecret);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // clean up vars
        $reflectionObject = new ReflectionObject($this);
        foreach ($reflectionObject->getProperties() as $property) {
            if (!$property->isStatic() && !str_starts_with($property->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $property->setValue($this, null);
            }
        }
    }

    private function getOpenApiSpecification()
    {
        if (null === $this->openapiValidator) {
            $this->openapiValidator = ValidatorBuilder::fromYamlFile(__DIR__.'/../../../openapi/specification.yaml')->getValidator();
        }

        return $this->openapiValidator;
    }

    protected function validateResponseAgainstOpenApiSpecification(string $path, string $method): void
    {
        $validator = $this->getOpenApiSpecification();
        $this->assertTrue($validator->validate(self::$frameworkBundleClient->getResponse(), $path, $method));
    }
}

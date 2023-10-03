<?php

namespace App\Pact\Listener;

use App\Service\Client\Sirius\SiriusApiGatewayClient;
use GuzzleHttp\Psr7\Uri;
use PhpPact\Broker\Service\BrokerHttpClient;
use PhpPact\Http\GuzzleClient;
use PhpPact\Standalone\Exception\MissingEnvVariableException;
use PhpPact\Standalone\MockService\MockServer;
use PhpPact\Standalone\MockService\MockServerConfigInterface;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PhpPact\Standalone\MockService\Service\MockServerHttpService;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;

/**
 * PACT listener that can be used with environment variables and easily attached to PHPUnit configuration.
 * Class PactTestListener
 */
class PactTestListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * Name of the test suite configured in your phpunit config.
     *
     * @var array
     */
    private $testSuiteNames;

    /** @var MockServerConfigInterface */
    private $mockServerConfig;

    /** @var bool */
    private $failed;

    /**
     * PactTestListener constructor.
     *
     * @param string[] $testSuiteNames test suite names that need evaluated with the listener
     *
     * @throws MissingEnvVariableException
     */
    public function __construct(array $testSuiteNames)
    {
        $this->testSuiteNames   = $testSuiteNames;
        $this->mockServerConfig = new MockServerEnvConfig();
    }

    /**
     * @param TestSuite $suite
     *
     * @throws \Exception
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $httpService = new MockServerHttpService(new GuzzleClient(), $this->mockServerConfig);
        $httpService->deleteAllInteractions();
    }

    public function addError(Test $test, \Throwable $t, float $time): void
    {
        $this->failed = true;
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->failed = true;
    }

    /**
     * Publish JSON results to PACT Broker and stop the Mock Server.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if (\in_array($suite->getName(), $this->testSuiteNames)) {
            if ($this->failed === true) {
                echo 'A unit test has failed. Skipping PACT file upload.';
            } else {
                $httpService = new MockServerHttpService(new GuzzleClient(), $this->mockServerConfig);
                $httpService->verifyInteractions();
                $json = $httpService->getPactJson();
                //requires these to exist
                if (($pactBrokerUri = \getenv('PACT_BROKER_BASE_URL')) &&
                    ($consumerVersion = \getenv('PACT_CONSUMER_VERSION'))) {
                    $clientConfig = [];
                    if (($user = \getenv('PACT_BROKER_HTTP_AUTH_USER')) &&
                        ($pass = \getenv('PACT_BROKER_HTTP_AUTH_PASSWORD'))
                    ) {
                        $clientConfig = [
                            'auth' => [$user, $pass],
                        ];
                    }
                    $headers = [];
                    $tag = SiriusApiGatewayClient::SIRIUS_API_GATEWAY_VERSION;
                    $pactBrokerUriFull = 'https://' . $pactBrokerUri . '/';
                    $client = new GuzzleClient($clientConfig);
                    $brokerHttpService = new BrokerHttpClient($client, new Uri($pactBrokerUriFull), $headers);
                    $brokerHttpService->tag($this->mockServerConfig->getConsumer(), $consumerVersion, $tag);
                    $brokerHttpService->publishJson($json, $consumerVersion);
                    print 'Pact file has been uploaded to the Broker successfully.';
                } else {
                    print 'One or more environment variables not set';
                }
            }
        }
    }
}

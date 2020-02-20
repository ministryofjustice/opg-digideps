<?php

namespace Consumer\Service;

use GuzzleHttp\Client;
use PhpPact\Consumer\InteractionBuilder;
use PhpPact\Consumer\Matcher\Matcher;
use PhpPact\Consumer\Model\ConsumerRequest;
use PhpPact\Consumer\Model\ProviderResponse;
use PhpPact\Standalone\MockService\MockServerEnvConfig;
use PHPUnit\Framework\TestCase;

class SiriusDocumentsContractTest extends TestCase
{
    /**
     * Example PACT test.
     *
     * @throws \Exception
     */
    public function testGetHelloString()
    {
        $matcher = new Matcher();

        // Create your expected request from the consumer.
        $request = new ConsumerRequest();
        $request
            ->setMethod('GET')
            ->setPath('/hello/Bob')
            ->addHeader('Content-Type', 'application/json');

        // Create your expected response from the provider.
        $response = new ProviderResponse();
        $response
            ->setStatus(200)
            ->addHeader('Content-Type', 'application/json')
            ->setBody([
                'message' => $matcher->term('Hello, Bob', '(Hello, )[A-Za-z]')
            ]);

        // Create a configuration that reflects the server that was started. You can create a custom MockServerConfigInterface if needed.
        $config  = new MockServerEnvConfig();
        $builder = new InteractionBuilder($config);
        $builder
            ->uponReceiving('A get request to /hello/{name}')
            ->with($request)
            ->willRespondWith($response); // This has to be last. This is what makes an API request to the Mock Server to set the interaction.

        // ------------------

        $client = new Client(['base_uri' => $config->getBaseUri()]);
        $response = $client->get('/hello/Bob', [
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);

        $builder->verify(); // This will verify that the interactions took place.

        $this->assertStringContainsString('Hello, Bob', strval($response->getBody())); // Make your assertions.
    }
}

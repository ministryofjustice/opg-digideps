<?php

namespace AppBundle\Service\Availability;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SiriusApiAvailability extends ServiceAvailabilityAbstract
{
    public function __construct(ContainerInterface $container)
    {
        $this->isHealthy = true;

        try {




            $url = new Uri('/v1/healthcheck');
            $request = new Request('GET', $url, $headers = [
                'Accept'        => 'application/json',
                'Content-type'  => 'application/json'
            ]);

            $provider = CredentialProvider::defaultProvider();
            $signer = new SignatureV4('execute-api', 'eu-west-1');

            // Sign the request with an AWS Authorization header.
            $signedRequest = $signer->signRequest($request, $provider()->wait());
            print '<pre>';
            print_r($signedRequest); exit;
            $response = $container->get('guzzle_api_gateway_client')->send($signedRequest);

            if (200 !== $response->getStatusCode()) {
                throw new \RuntimeException('returned HTTP code ' . $response->getStatusCode());
            }

        } catch (\Throwable $e) {
            $this->customMessage = $e->getMessage();
        }
    }

    public function getName()
    {
        return 'Sirius';
    }
}

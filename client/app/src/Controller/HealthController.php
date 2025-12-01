<?php

namespace App\Controller;

use App\Service\Availability\ApiAvailability;
use App\Service\Availability\ClamAvAvailability;
use App\Service\Availability\HtmlToPdfAvailability;
use App\Service\Availability\NotifyAvailability;
use App\Service\Availability\RedisAvailability;
use App\Service\Availability\SiriusApiAvailability;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/health-check')]
class HealthController extends AbstractController
{
    public function __construct(
        private readonly string $symfonyEnvironment,
        private readonly string $symfonyDebug,
        private readonly string $environment,
        private readonly LoggerInterface $logger,
        private readonly string $hostedEnv,
    ) {
    }

    #[Route(path: '', name: 'health_check', methods: ['GET'])]
    #[Template('@App/Health/health-check.html.twig')]
    public function containerHealthAction(): array
    {
        return ['status' => 'OK'];
    }

    #[Route(path: '/service', name: 'health_check_service', methods: ['GET'])]
    public function serviceHealthAction(
        ApiAvailability $apiAvailability,
        RedisAvailability $redisAvailability,
        ClamAvAvailability $clamAvailability,
        HtmlToPdfAvailability $htmlAvailability,
    ): ?Response {
        $services = [
            $apiAvailability,
            $redisAvailability,
        ];

        if ('admin' !== $this->environment) {
            $services[] = $clamAvailability;
            $services[] = $htmlAvailability;
        }

        [$healthy, $services, $errors] = $this->servicesHealth($services);

        $response = $this->render('@App/Health/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->symfonyEnvironment,
            'debug' => $this->symfonyDebug,
            'hostedEnv' => $this->hostedEnv,
        ]);

        $response->setStatusCode($healthy ? 200 : 503);

        return $response;
    }

    #[Route(path: '/dependencies', name: 'health_check_dependency', methods: ['GET'])]
    public function dependencyHealthAction(
        NotifyAvailability $notifyAvailability,
        SiriusApiAvailability $siriusAvailability,
    ): ?Response {
        $services = [
            $notifyAvailability,
        ];

        if ('admin' !== $this->environment) {
            $services[] = $siriusAvailability;
        }

        [$healthy, $services, $errors] = $this->servicesHealth($services);

        $response = $this->render('@App/Health/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->symfonyEnvironment,
            'debug' => $this->symfonyDebug,
            'hostedEnv' => $this->hostedEnv,
        ]);

        $response->setStatusCode($healthy ? 200 : 503);

        return $response;
    }

    #[Route(path: '/pingdom', name: 'health_check_pingdom', methods: ['GET'])]
    public function healthCheckXmlAction(
        ApiAvailability $apiAvailability,
        NotifyAvailability $notifyAvailability,
        RedisAvailability $redisAvailability,
    ): ?Response {
        $services = [
            $apiAvailability,
            $redisAvailability,
            $notifyAvailability,
        ];
        [$healthy, $services, $errors, $time] = $this->servicesHealth($services);

        $response = $this->render('@App/Health/pingdom.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERRORS: ',
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @return array [true if healthy, services array, string with errors, time in secs]
     */
    private function servicesHealth(array $services): array
    {
        $start = microtime(true);

        $healthy = true;
        $logResponses = false;
        $errors = [];
        $logObject = 'Availability Warning - {[';

        foreach ($services as $service) {
            $startServiceTime = microtime(true);

            $service->ping();

            if (!$service->isHealthy()) {
                $logResponses = true;
                $healthy = false;
                $errors[] = $service->getErrors();
            }
            $serviceTimeTaken = (microtime(true) - $startServiceTime);
            $logObject = $logObject . sprintf(
                '["service": "%s", "time": "%s", error: "%s"],',
                $service->getName(),
                round($serviceTimeTaken, 3),
                $service->getErrors()
            );
        }

        if ($logResponses) {
            $this->logger->warning(rtrim($logObject, ',') . ']}');
        }

        return [$healthy, $services, $errors, microtime(true) - $start];
    }
}

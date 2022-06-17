<?php

namespace App\Controller;

use App\Service\Availability\ApiAvailability;
use App\Service\Availability\ClamAvAvailability;
use App\Service\Availability\HtmlToPdfAvailability;
use App\Service\Availability\NotifyAvailability;
use App\Service\Availability\RedisAvailability;
use App\Service\Availability\SiriusApiAvailability;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
{
    public function __construct(
        private string $symfonyEnvironment,
        private string $symfonyDebug,
        private string $environment,
        private LoggerInterface $logger,
        private string $hostedEnv
    ) {
    }

    /**
     * @Route("/availability", methods={"GET"})
     *
     * @return Response|null
     */
    public function availabilityAction(
        ApiAvailability $apiAvailability,
        NotifyAvailability $notifyAvailability,
        RedisAvailability $redisAvailability,
        SiriusApiAvailability $siriusAvailability,
        ClamAvAvailability $clamAvailability,
        HtmlToPdfAvailability $htmlAvailability
    ) {
        $services = [
            $apiAvailability,
            $redisAvailability,
            $notifyAvailability,
        ];

        if ('admin' !== $this->environment) {
            $services[] = $siriusAvailability;
            $services[] = $clamAvailability;
            $services[] = $htmlAvailability;
        }

        list($healthy, $services, $errors) = $this->servicesHealth($services);

        $response = $this->render('@App/Manage/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->symfonyEnvironment,
            'debug' => $this->symfonyDebug,
            'hostedEnv' => $this->hostedEnv,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
    }

    /**
     * @Route("/availability/pingdom", methods={"GET"})
     *
     * @return Response|null
     */
    public function healthCheckXmlAction(
        ApiAvailability $apiAvailability,
        NotifyAvailability $notifyAvailability,
        RedisAvailability $redisAvailability
    ) {
        $services = [
            $apiAvailability,
            $redisAvailability,
            $notifyAvailability,
        ];
        list($healthy, $services, $errors, $time) = $this->servicesHealth($services);

        $response = $this->render('@App/Manage/health-check.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERRORS: ',
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/elb", name="manage-elb", methods={"GET"})
     * @Template("@App/Manage/elb.html.twig")
     */
    public function elbAction()
    {
        return ['status' => 'OK'];
    }

    /**
     * @return array [true if healthy, services array, string with errors, time in secs]
     */
    private function servicesHealth($services)
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
                if ('Sirius' != $service->getName()) {
                    $healthy = false;
                }
                $errors[] = $service->getErrors();
            }
            $serviceTimeTaken = (microtime(true) - $startServiceTime);
            $logObject = $logObject.sprintf(
                    '["service": "%s", "time": "%s", error: "%s"],',
                    $service->getName(),
                    round($serviceTimeTaken, 3),
                    $service->getErrors()
                );
        }

        if ($logResponses) {
            $this->logger->warning(strval(rtrim($logObject, ',').']}'));
        }

        return [$healthy, $services, $errors, microtime(true) - $start];
    }
}

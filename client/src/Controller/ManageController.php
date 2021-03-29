<?php

namespace App\Controller;

use App\Service\Availability\ApiAvailability;
use App\Service\Availability\ClamAvAvailability;
use App\Service\Availability\NotifyAvailability;
use App\Service\Availability\RedisAvailability;
use App\Service\Availability\SiriusApiAvailability;
use App\Service\Availability\WkHtmlToPdfAvailability;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
{
    private string $symfonyEnvironment;
    private string $symfonyDebug;
    private string $environment;

    public function __construct(
        string $symfonyEnvironment,
        string $symfonyDebug,
        string $environment
    ) {
        $this->symfonyEnvironment = $symfonyEnvironment;
        $this->symfonyDebug = $symfonyDebug;
        $this->environment = $environment;
    }

    /**
     * @Route("/availability", methods={"GET"})
     *
     * @param ApiAvailability $apiAvailability
     * @param NotifyAvailability $notifyAvailability
     * @param RedisAvailability $redisAvailability
     * @param SiriusApiAvailability $siriusAvailability
     * @param ClamAvAvailability $clamAvailability
     * @param WkHtmlToPdfAvailability $wkHtmlAvailability
     * @return Response|null
     */
    public function availabilityAction(
        ApiAvailability $apiAvailability,
        NotifyAvailability $notifyAvailability,
        RedisAvailability $redisAvailability,
        SiriusApiAvailability $siriusAvailability,
        ClamAvAvailability $clamAvailability,
        WkHtmlToPdfAvailability $wkHtmlAvailability
    ) {
        $services = [
            $apiAvailability,
            $redisAvailability,
            $notifyAvailability
        ];

        if ($this->environment !== 'admin') {
            $services[] = $siriusAvailability;
            $services[] = $clamAvailability;
            $services[] = $wkHtmlAvailability;
        }

        list($healthy, $services, $errors) = $this->servicesHealth($services);

        $response = $this->render('@App/Manage/availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->symfonyEnvironment,
            'debug' => $this->symfonyDebug
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
    }

    /**
     * @Route("/availability/pingdom", methods={"GET"})
     *
     * @param ApiAvailability $apiAvailability
     * @param NotifyAvailability $notifyAvailability
     * @param RedisAvailability $redisAvailability
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
            $notifyAvailability
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
        $errors = [];

        foreach ($services as $service) {
            if (!$service->isHealthy()) {
                if ($service->getName() != 'Sirius') {
                    $healthy = false;
                }
                $errors[] = $service->getErrors();
            }
        }

        return [$healthy, $services, $errors, microtime(true) - $start];
    }
}

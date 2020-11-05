<?php

namespace AppBundle\Controller;

use AppBundle\Service\Availability\ApiAvailability;
use AppBundle\Service\Availability\ClamAvAvailability;
use AppBundle\Service\Availability\NotifyAvailability;
use AppBundle\Service\Availability\RedisAvailability;
use AppBundle\Service\Availability\SiriusApiAvailability;
use AppBundle\Service\Availability\WkHtmlToPdfAvailability;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
{
    public function __construct(){}

    /**
     * @Route("/availability", methods={"GET"})
     *
     * @param ContainerInterface $container
     * @param ApiAvailability $apiAvailability
     * @param NotifyAvailability $notifyAvailability
     * @param RedisAvailability $redisAvailability
     *
     * @return Response|null
     */
    public function availabilityAction(
        ContainerInterface $container,
        ApiAvailability $apiAvailability,
        NotifyAvailability $notifyAvailability,
        RedisAvailability $redisAvailability
    )
    {

        $services = [
            $apiAvailability,
            $redisAvailability,
            $notifyAvailability
        ];

        if ($container->getParameter('env') !== 'admin') {
            $services[] = $container->get(SiriusApiAvailability::class);
            $services[] = $container->get(ClamAvAvailability::class);
            $services[] = $container->get(WkHtmlToPdfAvailability::class);
        }

        list($healthy, $services, $errors) = $this->servicesHealth($services);

        $response = $this->render('AppBundle:Manage:availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
            'environment' => $this->get('kernel')->getEnvironment(),
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
    )
    {
        $services = [
            $apiAvailability,
            $redisAvailability,
            $notifyAvailability
        ];
        list($healthy, $services, $errors, $time) = $this->servicesHealth($services);

        $response = $this->render('AppBundle:Manage:health-check.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERRORS: ',
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @Route("/elb", name="manage-elb", methods={"GET"})
     * @Template("AppBundle:Manage:elb.html.twig")
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
                $healthy = false;
                $errors[] = $service->getErrors();
            }
        }

        return [$healthy, $services, $errors, microtime(true) - $start];
    }
}

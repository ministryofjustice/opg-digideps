<?php

namespace AppBundle\Controller;

use AppBundle\Service\Availability\ApiAvailability;
use AppBundle\Service\Availability\ClamAvAvailability;
use AppBundle\Service\Availability\NotifyAvailability;
use AppBundle\Service\Availability\RedisAvailability;
use AppBundle\Service\Availability\SiriusApiAvailability;
// use AppBundle\Service\Availability\WkHtmlToPdfAvailability;
// use Psr\Container\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
{
    /** @var array<ServiceAvailabilityAbstract> */
    private $services = [];

    public function __construct(
        ContainerInterface $container,
        ApiAvailability $apiAvailability,
        // ClamAvAvailability $clamAvAvailability,
        NotifyAvailability $notifyAvailability,
        RedisAvailability $redisAvailability,
        SiriusApiAvailability $siriusApiAvailability
        // WkHtmlToPdfAvailability $wkHtmlToPdfAvailability
    )
    {
        $this->services = [
            $apiAvailability,
            $redisAvailability,
            $siriusApiAvailability,
            $notifyAvailability
        ];

        if ($container->getParameter('env') !== 'admin') {
        //     $this->services[] = $clamAvAvailability;
        //     $this->services[] = $wkHtmlToPdfAvailability;
        }
    }

    /**
     * @Route("/availability", methods={"GET"})
     */
    public function availabilityAction()
    {
        list($healthy, $services, $errors) = $this->servicesHealth();

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
     */
    public function healthCheckXmlAction()
    {
        list($healthy, $services, $errors, $time) = $this->servicesHealth();

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
    private function servicesHealth()
    {
        $start = microtime(true);

        $healthy = true;
        $errors = [];

        foreach ($this->services as $service) {
            if (!$service->isHealthy()) {
                $healthy = false;
                $errors[] = $service->getErrors();
            }
        }

        return [$healthy, $this->services, $errors, microtime(true) - $start];
    }
}

<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
{
    /**
     * @Route("/availability")
     * @Method({"GET"})
     * @Template
     */
    public function availabilityAction()
    {
        list($healthy, $errors, $services) = $this->servicesHealth();

        $response = $this->render('AppBundle:Manage:availability.html.twig', [
            'services' => $services,
        ]);

        $response->setStatusCode($healthy ? 200 : 500);

        return $response;
    }

    /**
     * @Route("/elb", name="manage-elb")
     * @Method({"GET"})
     * @Template()
     */
    public function elbAction()
    {
        return ['status' => 'OK'];
    }

    /**
     * @Route("/availability/pingdom")
     * @Method({"GET"})
     */
    public function healthCheckXmlAction()
    {
        list($healthy, $errors, $services, $time) = $this->servicesHealth();

        $response = $this->render('AppBundle:Manage:health-check.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERROR: '.$errors,
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @return array [boolean isHealty, string errors, array services, time in secs]
     */
    private function servicesHealth()
    {
        $start = microtime(true);

        $services = [
            new \AppBundle\Service\Availability\RedisAvailability($this->container),
            new \AppBundle\Service\Availability\ApiAvailability($this->container),
        ];

        $healthy = true;
        $errors = [];

        foreach ($services as $service) {
            if (!$service->isHealthy()) {
                $healthy = false;
                $errors[] = $service->getErrors();
            }
        }

        return [$healthy, implode('. ', $errors), $services, microtime(true) - $start];
    }
}

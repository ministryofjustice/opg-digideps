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
        list($healthy, $services, $errors) = $this->servicesHealth();

        $response = $this->render('AppBundle:Manage:availability.html.twig', [
            'services' => $services,
            'errors' => $errors,
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
        list($healthy, $errors, $time) = $this->servicesHealth();

        $response = $this->render('AppBundle:Manage:health-check.xml.twig', [
            'status' => $healthy ? 'OK' : 'ERROR: ' . $errors,
            'time' => $time * 1000,
        ]);
        $response->setStatusCode($healthy ? 200 : 500);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }


    /**
     * @return array [true if healthy, services array, string with errors, time in secs]
     */
    private function servicesHealth()
    {
        $start = microtime(true);

        $services = [
            new \AppBundle\Service\Availability\RedisAvailability($this->container),
            new \AppBundle\Service\Availability\ApiAvailability($this->container),
            new \AppBundle\Service\Availability\SmtpAvailability($this->container, 'mailer.transport.smtp.default'),
            new \AppBundle\Service\Availability\SmtpAvailability($this->container, 'mailer.transport.smtp.secure'),
        ];
        if ($this->container->getParameter('env') !== 'admin') {
            $services[] = new \AppBundle\Service\Availability\WkHtmlToPdfAvailability($this->container);
        }

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
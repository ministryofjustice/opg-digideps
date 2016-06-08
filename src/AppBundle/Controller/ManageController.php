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
        
        //TODO move to service above
        list($smtpDefaultHealthy, $smtpDefaultError) = $this->smtpDefaultInfo();
        list($smtpSecureHealthy, $smtpSecureError) = $this->smtpSecureInfo();
        list($wkHtmlToPdfInfoHealthy, $wkHtmlToPdfInfoError) = $this->wkHtmlToPdfInfo();
        
        if (!$smtpDefaultHealthy) {
            $healthy = false;
            $errors[] = 'SMTP: '.$smtpDefaultError;
        }
        
        if (!$smtpSecureHealthy) {
            $healthy = false;
            $errors[] = 'SMTP SECURE: '.$smtpSecureError;
        }
        
        if (!$wkHtmlToPdfInfoHealthy) {
            $healthy = false;
            $errors[] = 'wkHtmlToPd: '.$wkHtmlToPdfInfoError;
        }
        
        return [$healthy, $services, implode('. ', $errors), microtime(true) - $start];
    }
    
    
    /**
     * @return array [boolean healthy, error string]
     */
    private function smtpDefaultInfo()
    {
        try {
            $transport = $this->container->get('mailer.transport.smtp.default'); /* @var $transport \Swift_SmtpTransport */
            $transport->start();
            $transport->stop();

            return [true, ''];
        } catch (\Exception $e) {
            return [false, 'SMTP default Error: '.$e->getMessage()];
        }
    }

    /**
     * @return array [boolean healthy, error string]
     */
    private function smtpSecureInfo()
    {
        try {
            $transport = $this->container->get('mailer.transport.smtp.secure'); /* @var $transport \Swift_SmtpTransport */
            $transport->start();
            $transport->stop();

            return [true, ''];
        } catch (\Exception $e) {
            return [false, 'SMTP Secure Error: '.$e->getMessage()];
        }
    }
    
    
    
    /**
     * @return array [boolean healthy, error string]
     */
    private function wkHtmlToPdfInfo()
    {
        try {
            $ret = $this->container->get('wkhtmltopdf')->isAlive();
            if (!$ret) {
                throw new \RuntimeException('service down or created an invalid PDF');
            }

            return [true, ''];
        } catch (\Exception $e) {
            return [false, 'wkhtmltopdf HTTP Error: '.$e->getMessage()];
        }
    }
}

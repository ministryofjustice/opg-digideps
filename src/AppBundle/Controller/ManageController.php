<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/manage")
 */
class ManageController extends RestController
{
    /**
     * @Route("/availability")
     * @Method({"GET"})
     */
    public function availabilityAction()
    {
        list($dbHealthy, $dbError) = $this->dbInfo();
        list($smtpDefaultHealthy, $smtpDefaultError) = $this->smtpDefaultInfo();
        list($smtpSecureHealthy, $smtpSecureError) = $this->smtpSecureInfo();
        list($wkHtmlToPdfInfoHealthy, $wkHtmlToPdfInfoError) = $this->wkHtmlToPdfInfo();

        $data = [
            'healthy' => $dbHealthy && $smtpDefaultHealthy && $smtpSecureHealthy && $wkHtmlToPdfInfoHealthy,
            'errors' => implode("\n", array_filter([$dbError, $smtpDefaultError, $smtpSecureError, $wkHtmlToPdfInfoError])),
        ];

        return $data;
    }

    /**
     * @Route("/elb", name="manage-elb")
     * @Method({"GET"})
     */
    public function elbAction()
    {
        return 'ok';
    }

    /**
     * @return array [boolean healthy, error string]
     */
    private function dbInfo()
    {
        try {
            $this->getDoctrine()->getRepository('AppBundle\Entity\User')->findAll();

            return [true, ''];
        } catch (\Exception $e) {
            // customise error message if possible
            $returnMessage = 'Database generic error';
            if ($e instanceof \PDOException && $e->getCode() === 7) {
                $returnMessage = 'Database service not reachabe ('.$e->getMessage().')';
            }
            if ($e instanceof \Doctrine\DBAL\DBALException) {
                $returnMessage = 'Database schema error (dd_user table not found) ('.$e->getMessage().')';
            }

            // log real error message
            $this->get('logger')->error($e->getMessage());

            return [false, $returnMessage];
        }
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
        $this->container->get('wkhtmltopdf')->isAlive();

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

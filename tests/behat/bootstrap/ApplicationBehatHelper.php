<?php

use Application\Entity\User;
use Zend\ServiceManager\ServiceManager;

/**
 * Handles fixtures added by behat via the MainContext
 * 
 * contains the reference to the models to call their functions
 * 
 * when the alpha models are refactored and simplified, this class can be refactored and splitter around.
 * until then, better to keep things in the sample place for simplicity
 */
class ApplicationBehatHelper
{
    public function __construct(ServiceManager $sm)
    {
        throw new Exception('TO IMPLEMENT');
        
        $this->sm = $sm;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->sm->get('Config');
    }

    /** @var  Zend\ServiceManager\ServiceManager */
    private $sm;

    /** @var Doctrine\ORM\EntityManager */
    private $em;

    /**
     * Keeps a copy of the added user role => id
     * @var array
     */
    private $addedUsers = array();


    /**
     * @return Zend\ServiceManager\ServiceManage
     */
    public function getSm()
    {
        return $this->sm;
    }

    private function getMailPath()
    {
        $config = $this->sm->get('Config');

        return $config['mail_transport_file']['path'];
    }

    /**
     * @return Mailentry
     */
    public function getLatestEmail()
    {
        $mailPath = $this->getMailPath();

        $content = file_get_contents($mailPath);

        if (!$content) {
            throw new \Exception("No mail have been sent");
        }
        return new MailEntry($content);
    }

    public function cleanLatestEmail()
    {
        $mailPath = $this->getMailPath();

        $ret = file_put_contents($mailPath, "RESET\n");
        if (!$ret) {
            throw new \Exception("Cannot re-write into $mailPath");
        }
        file_put_contents($mailPath, "");
    }

    private function getLogPath()
    {
        $config = $this->sm->get('Config');

        return $config['logWriters'][0]['options']['stream'];
    }

    public function clearAppLog()
    {
        if (!file_exists($this->getLogPath())) {
            throw new \Exception("Log " . $this->getLogPath() . " not found, cannot run this scenario");
        }
        $ret = file_put_contents($this->getLogPath(), " \n");
        if (!$ret) {
            throw new \Exception('Cannot reset log. path: ' . $this->getLogPath());
        }
    }

    public function getLogContent()
    {
        return file_get_contents($this->getLogPath());
    }

}
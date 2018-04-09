<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Controller\AbstractController;
use AppBundle\Entity as EntityDir;
use AppBundle\Service\File\MultiDocumentZipFileCreator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/admin/service-notification")
 */
class ServiceNotificationController extends AbstractController
{
    /**
     * @Route("", name="service_notification_home")
     * @Template
     */
    public function indexAction(Request $request)
    {

    }

}

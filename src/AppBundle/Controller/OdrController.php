<?php

namespace AppBundle\Controller;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\ReportStatusService;
use Doctrine\Common\Util\Debug;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

class OdrController extends AbstractController
{
    /**
     * @Route("/odr/overview", name="odr_overview")
     * @Template("AppBundle:Odr:overview.html.twig")
     */
    public function overviewAction()
    {
        $client = $this->getClientOrThrowException();
        $odr = $this->getOdr($client->getId(), ['odr', 'visits-care']);

        if ($odr->getSubmitted()) {
            throw new \RuntimeException('Odr already submitted and not editable.');
        }
        $odrStatus = new OdrStatusService($odr);

        return [
            'client' => $client,
            'odr' => $odr,
            'odrStatus' => $odrStatus,
        ];
    }
}

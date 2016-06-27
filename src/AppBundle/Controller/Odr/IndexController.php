<?php

namespace AppBundle\Controller\Odr;

use AppBundle\Entity as EntityDir;
use AppBundle\Form as FormDir;
use AppBundle\Model as ModelDir;
use AppBundle\Service\OdrStatusService;
use AppBundle\Service\ReportStatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    private static $odrGroupsForValidation = [
        'odr',
        'visits-care'
    ];

    /**
     * //TODO move view into Odr directory when branches are integrated
     * @Route("/reports", name="index-odr")
     * @Template("AppBundle:Report:indexOdr.html.twig")
     */
    public function indexOdrAction()
    {
        $clients = $this->getUser()->getClients();
        $client = !empty($clients) ? $clients[0] : null;

        $reports = $client ? $this->getReportsIndexedById($client, ['basic']) : [];
        arsort($reports);

        return [
            'client' => $client,
            'reports' => $reports,
        ];
    }

    /**
     * @Route("/odr/overview", name="odr_overview")
     * @Template("AppBundle:Odr:overview.html.twig")
     */
    public function overviewAction()
    {
        $client = $this->getClientOrThrowException();
        $odr = $this->getOdr($client->getId(), self::$odrGroupsForValidation);

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

    /**
     * @Route("/odr/submit", name="odr_submit")
     * @Template()
     */
    public function submitAction(Request $request)
    {
        $client = $this->getClientOrThrowException();
        $odr = $this->getOdr($client->getId(), self::$odrGroupsForValidation);

        if ($odr->getSubmitted()) {
            throw new \RuntimeException('ODR already submitted and not editable.');
        }

        $odr->setSubmitted(true)->setSubmitDate(new \DateTime());
        $this->getRestClient()->put('odr/' . $odr->getId() . '/submit', $odr, [
            'deserialise_group' => 'submit',
        ]);

        return $this->redirect($this->generateUrl('index-odr'));
    }
}

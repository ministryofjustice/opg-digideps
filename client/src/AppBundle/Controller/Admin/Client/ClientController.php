<?php

namespace AppBundle\Controller\Admin\Client;

use AppBundle\Controller\AbstractController;
use AppBundle\Service\Audit\AuditEvents;
use AppBundle\Service\Client\Internal\ClientApi;
use AppBundle\Service\Client\RestClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/client")
 */
class ClientController extends AbstractController
{
    /** @var RestClient */
    private $restClient;

    /** @var ClientApi */
    private $clientApi;

    public function __construct(
        RestClient $restClient,
        ClientApi $clientApi
    ) {
        $this->restClient = $restClient;
        $this->clientApi = $clientApi;
    }

    /**
     * @Route("/{id}/details", name="admin_client_details", requirements={"id":"\d+"})
     * //TODO define Security group (AD to remove?)
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @param string $id
     *
     * @Template("AppBundle:Admin/Client/Client:details.html.twig")
     *
     * @return array
     */
    public function detailsAction($id)
    {
        $client = $this->clientApi->getWithUsers($id);

        return [
            'client'      => $client,
            'namedDeputy' => $client->getDeputy()
        ];
    }

    /**
     * @Route("/case-number/{caseNumber}/details", name="admin_client_by_case_number_details")
     * @Security("has_role('ROLE_ADMIN') or has_role('ROLE_AD')")
     * @param string $caseNumber
     *
     * @return RedirectResponse
     */
    public function detailsByCaseNumberAction(string $caseNumber)
    {
        $client = $this->clientApi->getByCaseNumber($caseNumber);

        return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
    }

    /**
     * @Route("/{id}/discharge", name="admin_client_discharge", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     * @param $id
     *
     * @Template("AppBundle:Admin/Client/Client:discharge.html.twig")
     *
     * @return array
     */
    public function dischargeAction($id)
    {
        $client = $this->clientApi->getWithUsersV2($id);

        return [
            'client' => $client,
            'namedDeputy' => $client->getDeputy()
        ];
    }

    /**
     * @Route("/{id}/discharge-confirm", name="admin_client_discharge_confirm", requirements={"id":"\d+"})
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     *
     * @param $id
     * @param AuditEvents $auditEvents
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function dischargeConfirmAction($id)
    {
        $this->clientApi->delete($id, AuditEvents::TRIGGER_ADMIN_BUTTON);
        return $this->redirectToRoute('admin_client_search');
    }
}

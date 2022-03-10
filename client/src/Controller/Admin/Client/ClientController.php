<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\RestClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @param string $id
     *
     * @Template("@App/Admin/Client/Client/details.html.twig")
     *
     * @return array
     */
    public function detailsAction($id)
    {
        $client = $this->clientApi->getWithUsersV2($id);

        return [
            'client' => $client,
            'namedDeputy' => $client->getDeputy(),
        ];
    }

    /**
     * @Route("/case-number/{caseNumber}/details", name="admin_client_by_case_number_details")
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
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
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     *
     * @param $id
     *
     * @Template("@App/Admin/Client/Client/discharge.html.twig")
     *
     * @return array
     */
    public function dischargeAction($id)
    {
        $client = $this->clientApi->getWithUsersV2($id);

        return [
            'client' => $client,
            'namedDeputy' => $client->getDeputy(),
        ];
    }

    /**
     * @Route("/{id}/discharge-confirm", name="admin_client_discharge_confirm", requirements={"id":"\d+"})
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     *
     * @param $id
     * @param AuditEvents $auditEvents
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function dischargeConfirmAction($id)
    {
        $this->clientApi->delete($id, AuditEvents::TRIGGER_ADMIN_BUTTON);

        return $this->redirectToRoute('admin_client_search');
    }

    /**
     * @Route("/{id}/archived", name="admin_client_archived", requirements={"id":"\d+"})
     * @Security("is_granted('ROLE_ADMIN') or has_role('ROLE_AD')")
     *
     * @Template("@App/Admin/Client/Client/archived.html.twig")
     */
    public function unarchiveAction(string $id): array
    {
        $client = $this->clientApi->getWithUsersV2($id);

        return [
            'client' => $client,
            'namedDeputy' => $client->getDeputy(),
        ];
    }
}

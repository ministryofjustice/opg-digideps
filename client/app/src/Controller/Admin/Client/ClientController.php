<?php

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\UserApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/client")
 */
class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly UserApi $userApi,
    ) {
    }

    /**
     * @Route("/{id}/details", requirements={"id":"\d+"}, name="admin_client_details")
     * //TODO define Security group (AD to remove?)
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Client/Client/details.html.twig")
     */
    public function detailsAction(int $id): array|RedirectResponse
    {
        $client = $this->clientApi->getWithUsersV2($id);
        if (null !== $client->getArchivedAt()) {
            return $this->redirectToRoute('admin_client_archived', ['id' => $client->getId()]);
        }

        $deputy = $client->getDeputy();

        if ($deputy instanceof User) {
            if (false == $deputy->getIsPrimary()) {
                $deputy = $this->userApi->getPrimaryUserAccount($deputy->getDeputyUid());
            }
        }

        return [
            'client' => $client,
            'deputy' => $deputy,
        ];
    }

    /**
     * @Route("/case-number/{caseNumber}/details", name="admin_client_by_case_number_details")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     */
    public function detailsByCaseNumberAction(string $caseNumber): RedirectResponse
    {
        $client = $this->clientApi->getByCaseNumber($caseNumber);

        return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
    }

    /**
     * @Route("/{id}/discharge", requirements={"id":"\d+"}, name="admin_client_discharge")
     *
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     *
     * @Template("@App/Admin/Client/Client/discharge.html.twig")
     */
    public function dischargeAction($id): array
    {
        $client = $this->clientApi->getWithUsersV2($id);

        return [
            'client' => $client,
            'deputy' => $client->getDeputy(),
        ];
    }

    /**
     * @Route("/{id}/discharge-confirm", requirements={"id":"\d+"}, name="admin_client_discharge_confirm")
     *
     * @Security("is_granted('ROLE_ADMIN_MANAGER')")
     *
     * @throws \Exception
     */
    public function dischargeConfirmAction($id): RedirectResponse
    {
        $this->clientApi->delete($id, AuditEvents::TRIGGER_ADMIN_BUTTON);

        return $this->redirectToRoute('admin_client_search');
    }

    /**
     * @Route("/{id}/archived", requirements={"id":"\d+"}, name="admin_client_archived")
     *
     * @Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')")
     *
     * @Template("@App/Admin/Client/Client/archived.html.twig")
     */
    public function archivedAction(string $id): RedirectResponse|array
    {
        $client = $this->clientApi->getWithUsersV2($id);
        if (null === $client->getArchivedAt()) {
            return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
        }

        return [
            'client' => $client,
            'deputy' => $client->getDeputy(),
        ];
    }

    /**
     * @Route("/{id}/unarchived", requirements={"id":"\d+"}, name="admin_client_unarchived")
     *
     * @Security ("is_granted('ROLE_ADMIN_MANAGER')")
     *
     * @Template("@App/Admin/Client/Client/unarchived.html.twig")
     */
    public function unarchiveAction(string $id): array|RedirectResponse
    {
        $client = $this->clientApi->getWithUsersV2($id);
        if (null === $client->getArchivedAt()) {
            return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
        }

        $this->clientApi->unarchiveClient($id);

        return [];
    }
}

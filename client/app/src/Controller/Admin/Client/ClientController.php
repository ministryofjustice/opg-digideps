<?php

declare(strict_types=1);

namespace App\Controller\Admin\Client;

use App\Controller\AbstractController;
use App\Entity\User;
use App\Service\Audit\AuditEvents;
use App\Service\Client\Internal\ClientApi;
use App\Service\Client\Internal\UserApi;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/admin/client')]
class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly UserApi $userApi,
    ) {
    }

    #[Route(path: '/{id}/details', name: 'admin_client_details', requirements: ['id' => '\d+'])] // //TODO define Security group (AD to remove?)
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    #[Template('@App/Admin/Client/Client/details.html.twig')]
    public function detailsAction(int $id): RedirectResponse|array
    {
        $client = $this->clientApi->getWithUsersV2($id);
        if (null !== $client->getArchivedAt()) {
            return $this->redirectToRoute('admin_client_archived', ['id' => $client->getId()]);
        }

        $deputy = $client->getDeputy();

        if ($deputy instanceof User) {
            if (!$deputy->getIsPrimary()) {
                $deputy = $this->userApi->getPrimaryUserAccount($deputy->getDeputyUid());
            }
        }

        return [
            'client' => $client,
            'deputy' => $deputy,
        ];
    }

    #[Route(path: '/case-number/{caseNumber}/details', name: 'admin_client_by_case_number_details')]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    public function detailsByCaseNumberAction(string $caseNumber): RedirectResponse
    {
        $client = $this->clientApi->getByCaseNumber($caseNumber);

        return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
    }

    #[Route(path: '/{id}/discharge', name: 'admin_client_discharge', requirements: ['id' => '\d+'])]
    #[IsGranted(attribute: 'ROLE_ADMIN_MANAGER')]
    #[Template('@App/Admin/Client/Client/discharge.html.twig')]
    public function dischargeAction(int $id): array
    {
        $client = $this->clientApi->getWithUsersV2($id);

        return [
            'client' => $client,
            'deputy' => $client->getDeputy(),
        ];
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/{id}/discharge-confirm', name: 'admin_client_discharge_confirm', requirements: ['id' => '\d+'])]
    #[IsGranted(attribute: 'ROLE_ADMIN_MANAGER')]
    public function dischargeConfirmAction(int $id): RedirectResponse
    {
        $this->clientApi->delete($id, AuditEvents::TRIGGER_ADMIN_BUTTON);

        return $this->redirectToRoute('admin_client_search');
    }

    #[Route(path: '/{id}/archived', name: 'admin_client_archived', requirements: ['id' => '\d+'])]
    #[IsGranted(attribute: new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_AD')"))]
    #[Template('@App/Admin/Client/Client/archived.html.twig')]
    public function archivedAction(int $id): RedirectResponse|array
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

    #[Route(path: '/{id}/unarchived', name: 'admin_client_unarchived', requirements: ['id' => '\d+'])]
    #[IsGranted(attribute: 'ROLE_ADMIN_MANAGER')]
    #[Template('@App/Admin/Client/Client/unarchived.html.twig')]
    public function unarchiveAction(string $id): ?RedirectResponse
    {
        $client = $this->clientApi->getWithUsersV2(intval($id));
        if (null === $client->getArchivedAt()) {
            return $this->redirectToRoute('admin_client_details', ['id' => $client->getId()]);
        }

        $this->clientApi->unarchiveClient($id);

        return null;
    }
}

<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Client;
use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ClientVoter extends Voter
{
    /** @var string */
    const VIEW = 'view';

    /** @var string */
    const EDIT = 'edit';

    /** @var string */
    const DELETE = 'delete';

    public function __construct(private Security $security)
    {
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]) && $subject instanceof Client;
    }

    /**
     * @param string $attribute
     * @param Client $client
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $client, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::EDIT => $this->canManage($client, $user),
            self::DELETE => $this->canDelete($user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    /**
     * @return bool
     */
    private function canManage(Client $client, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($user->isLayDeputy()) {
            return in_array($user->getId(), $client->getUserIds());
        }

        if ($client->userBelongsToClientsOrganisation($user)) {
            return true;
        }

        // todo-aie remove post DDPB-3050, when all access should be denied if ! userBelongsToClientsOrganisation
        if (!$this->clientsOrganisationActive($client) && in_array($user->getId(), $client->getUserIds())) {
            return true;
        }

        return false;
    }

    private function canDelete(User $user): bool
    {
        if ($user->isAdminManager() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    private function clientsOrganisationActive(Client $client): bool
    {
        $organisation = $client->getOrganisation();

        return $organisation instanceof Organisation && $organisation->isActivated();
    }
}

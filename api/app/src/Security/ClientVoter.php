<?php

namespace OPG\Digideps\Backend\Security;

use OPG\Digideps\Backend\Entity\Client;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * @extends Voter<string, Client>
 */
class ClientVoter extends Voter
{
    /** @var string */
    public const string VIEW = 'view';

    /** @var string */
    public const string EDIT = 'edit';

    /** @var string */
    public const string DELETE = 'delete';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE]) && $subject instanceof Client;
    }

    /**
     * @param Client $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
                return $this->canManage($subject, $user);
            case self::DELETE:
                return $this->canDelete($user);

            default:
                throw new \LogicException('This code should not be reached!');
        }
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

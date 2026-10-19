<?php

namespace OPG\Digideps\Backend\Security;

use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Organisation>
 */
class OrganisationVoter extends Voter
{
    public const string VIEW = 'view';
    public const string EDIT = 'edit';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT]) && $subject instanceof Organisation;
    }

    /**
     * @param Organisation $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return match ($attribute) {
            self::VIEW, self::EDIT => $this->canManage($subject, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canManage(Organisation $organisation, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if ($organisation->getUsers()->contains($user)) {
            return true;
        }

        return false;
    }
}

<?php

namespace App\Security;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * @extends Voter<string, Organisation>
 */
class OrganisationVoter extends Voter
{
    /** @var string */
    public const VIEW = 'view';

    /** @var string */
    public const EDIT = 'edit';

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

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
                return $this->canManage($subject, $user);

            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    /**
     * @return bool
     */
    private function canManage(Organisation $organisation, User $user)
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

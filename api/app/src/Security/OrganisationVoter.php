<?php

namespace App\Security;

use App\Entity\Organisation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class OrganisationVoter extends Voter
{
    /** @var string */
    public const VIEW = 'view';

    /** @var string */
    public const EDIT = 'edit';

    /** @var Security */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::VIEW, self::EDIT]) && $subject instanceof Organisation;
    }

    /**
     * @param string       $attribute
     * @param Organisation $organisation
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $organisation, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
            case self::EDIT:
                return $this->canManage($organisation, $user);

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

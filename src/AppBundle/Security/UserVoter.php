<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class UserVoter extends Voter
{
    const ADD_USER = 'add-user';
    const EDIT_USER = 'edit-user';
    const DELETE_USER = 'delete-user';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * UserVoter constructor.
     *
     * @param AccessDecisionManagerInterface $decisionManager
     */
    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    /**
     * Does this voter support the attribute?
     *
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        switch($attribute) {
            case self::ADD_USER:
            case self::DELETE_USER:
                return true;
            case self::EDIT_USER:
                // only vote on User objects inside this voter
                if ($attribute === self::EDIT_USER && $subject instanceof User) {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Vote on whether to grant attribute permission on subject
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $loggedInUser= $token->getUser();
        if (!$loggedInUser instanceof User) {
            // the loggedUSer must be logged in; if not, deny access
            return false;
        }

        if ($attribute === self::ADD_USER) {
            // only Named and Admin can add users
            return $this->decisionManager->decide($token, [User::ROLE_PA, USER::ROLE_PA_ADMIN]);
        }

        if ($attribute === self::DELETE_USER) {
            return $this->determineDeletePermission($loggedInUser, $subject);
        }

        if ($attribute === self::EDIT_USER) {
            return $this->determineEditPermission($loggedInUser, $subject);
        }

        return false;
    }

    /**
     * Determine whether logged in user can edit a subject user
     *
     * @param User $loggedInUser
     * @param User $subject
     * @return bool
     */
    private function determineEditPermission(User $loggedInUser, User $subject)
    {
        if ($subject->getId() === $loggedInUser->getId() &&
            $loggedInUser->getRoleName() !== User::ROLE_PA_TEAM_MEMBER) {
            // can always edit one's self except team members
            return true;
        }

        switch($loggedInUser->getRoleName()) {
            case User::ROLE_PA:
            case User::ROLE_ADMIN:
            case User::ROLE_AD:
                // Admin, Assisted and Named Deputies can always edit everyone. Replicated from populate user.
                return true;
            case User::ROLE_PA_ADMIN:
                // Admin can edit everyone except Named
                if ($subject->getRoleName() !== User::ROLE_PA) {
                    return true;
                }
                return false;
            case User::ROLE_PA_TEAM_MEMBER:
                // Team members can only edit themselves (See above)
                return false;
        }

        return false;
    }

    /**
     * Determine whether logged in user can delete a subject user
     *
     * @param User $loggedInUser
     * @param User $subject
     * @return bool
     */
    private function determineDeletePermission(User $loggedInUser, User $subject)
    {
        // Cannot remove oneself
        if ($subject->getId() === $loggedInUser->getId()) {
            return false;
        }

        switch($loggedInUser->getRoleName()) {
            case User::ROLE_PA:
                // Named deputies can remove anyone
                return true;
            case User::ROLE_PA_ADMIN:
                // Admin can remove everyone except Named
                if ($subject->getRoleName() !== User::ROLE_PA) {
                    return true;
                }
                return false;
            case User::ROLE_PA_TEAM_MEMBER:
                // Team members cannot remove anyone
                return false;
        }
        return false;
    }
}

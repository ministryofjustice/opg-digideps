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
        $loggedUser= $token->getUser();
        if (!$loggedUser instanceof User) {
            // the loggedUSer must be logged in; if not, deny access
            return Voter::ACCESS_DENIED;
        }

        if ($attribute === self::ADD_USER) {
            // only Named and Admin can add users
            return $this->decisionManager->decide($token, [User::ROLE_PA, USER::ROLE_PA_ADMIN]);
        }

        if ($attribute == self::EDIT_USER) {
            if ($subject->getId() === $loggedUser->getId()) {
                // can always edit one's self
                return Voter::ACCESS_GRANTED;
            }

            switch($loggedUser->getRoleName()) {
                case User::ROLE_PA:
                    // Named can always edit everyone
                    return Voter::ACCESS_GRANTED;
                case User::ROLE_PA_ADMIN:
                    // Admin can edit everyone except Named
                    if ($subject->getRoleName() !== User::ROLE_PA) {
                        return Voter::ACCESS_GRANTED;
                    }
                    break;
                case User::ROLE_PA_TEAM_MEMBER:
                    // Team members can only edit themselves (See above)
                    return Voter::ACCESS_DENIED;
            }
        }

        return Voter::ACCESS_DENIED;
    }
}

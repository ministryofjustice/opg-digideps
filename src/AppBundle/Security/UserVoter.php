<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const ADD = 'add';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::ADD, self::EDIT))) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $loggedUser= $token->getUser();

        if (!$loggedUser instanceof User) {
            // the loggedUSer must be logged in; if not, deny access
            return false;
        }

        $loggedUserRoleName = $loggedUser->getRoleName();

        if ($attribute == self::ADD) {
            // only Named and Admin can add users
            return in_array($loggedUserRoleName, [User::ROLE_PA, User::ROLE_PA_ADMIN]);
        }

        if ($attribute == self::EDIT) {
            switch($loggedUserRoleName) {
                // Named can always edit everyone
                case User::ROLE_PA:
                    return true;
                // Admin can edit everyone except Named
                case User::ROLE_PA_ADMIN:
                    return $subject->getRoleName() !== User::ROLE_PA;
                // Team members can only edit themselves
                case User::ROLE_PA_TEAM_MEMBER:
                    return $subject->getId() === $loggedUser->getId();
            }
        }

        throw new \LogicException('This code should not be reached!');
    }
}
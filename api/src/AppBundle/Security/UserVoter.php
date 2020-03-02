<?php declare(strict_types=1);

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const DELETE_USER = 'delete-user';

    /**
     * Does this voter support the attribute?
     *
     * @param  string $attribute
     * @param  mixed  $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        switch ($attribute) {
            case self::DELETE_USER:
                return true;
        }

        return false;
    }

    /**
     * Vote on whether to grant attribute permission on subject
     *
     * @param  string         $attribute
     * @param  mixed          $subject
     * @param  TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $loggedInUser= $token->getUser();
        if (!$loggedInUser instanceof User) {
            // the loggedUSer must be logged in; if not, deny access
            return false;
        }

        if ($attribute === self::DELETE_USER) {
            return $this->determineDeletePermission($loggedInUser, $subject);
        }

        return false;
    }

    /**
     * Determine whether logged in user can delete a subject user.
     *
     * Ensure any changes are mirrored in API version of this class.
     *
     * @param User $deletor
     * @param User $deletee
     * @return bool
     */
    private function determineDeletePermission(User $deletor, User $deletee)
    {
        if ($deletor->getId() === $deletee->getId()) {
            return false;
        }

        switch ($deletor->getRoleName()) {
            case User::ROLE_PA_NAMED:
            case User::ROLE_PA_ADMIN:
            case User::ROLE_PROF_NAMED:
            case User::ROLE_PROF_ADMIN:
                return $this->paProfNamedAdminPermissions($deletee);
            case User::ROLE_ADMIN:
            case User::ROLE_SUPER_ADMIN:
                return $this->adminSuperAdminPermissions($deletor, $deletee);
        }

        return false;
    }

    private function paProfNamedAdminPermissions(User $deletee): bool
    {
        switch ($deletee->getRoleName()) {
            case User::ROLE_LAY_DEPUTY:
            case User::ROLE_ADMIN:
            case User::ROLE_SUPER_ADMIN:
                return false;
        }

        return true;
    }

    private function adminSuperAdminPermissions(User $deletor, User $deletee): bool
    {
        switch ($deletee->getRoleName()) {
            case User::ROLE_PA:
            case User::ROLE_PA_TEAM_MEMBER:
            case User::ROLE_PA_NAMED:
            case User::ROLE_PA_ADMIN:
            case User::ROLE_PROF:
            case User::ROLE_PROF_TEAM_MEMBER:
            case User::ROLE_PROF_NAMED:
            case User::ROLE_PROF_ADMIN:
                return true;
            case User::ROLE_LAY_DEPUTY:
                return count($deletee->getClients()) <= 1 && !$deletee->hasReports() ? true : false;
        }

        return $deletor->getRoleName() === User::ROLE_SUPER_ADMIN ? true : false;
    }
}

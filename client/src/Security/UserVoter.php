<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const ADD_USER = 'add-user';
    const EDIT_USER = 'edit-user';
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

        switch ($attribute) {
            case self::ADD_USER:
            case self::EDIT_USER:
                return $this->determineAddEditPermission($loggedInUser, $subject);
            case self::DELETE_USER:
                return $this->determineDeletePermission($loggedInUser, $subject);
        }

        return false;
    }

    /**
     * Determine whether logged in user can add or edit a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
     *
     * @param User $deletor
     * @param User $deletee
     * @return bool
     */
    private function determineAddEditPermission(User $actor, User $subject)
    {
        if ($actor->getId() === $subject->getId()) {
            return true;
        }

        switch ($actor->getRoleName()) {
            case User::ROLE_SUPER_ADMIN:
                return true;
            case User::ROLE_ELEVATED_ADMIN:
                if ($subject->isSuperAdmin()) {
                    return false;
                }
                return true;
            case User::ROLE_ADMIN:
            case User::ROLE_AD:
                if (
                    $subject->isSuperAdmin() ||
                    $subject->isElevatedAdmin()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PA:
            case User::ROLE_PA_NAMED:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isDeputyProf()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PROF:
            case User::ROLE_PROF_NAMED:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isDeputyPa()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PA_ADMIN:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isPaNamedDeputy() ||
                    $subject->isPaTopRole() ||
                    $subject->isDeputyProf()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PROF_ADMIN:
                if (
                    $subject->hasAdminRole() ||
                    $subject->isLayDeputy() ||
                    $subject->isProfNamedDeputy() ||
                    $subject->isProfTopRole() ||
                    $subject->isDeputyPa()
                ) {
                    return false;
                }
                return true;
            default:
                return false;
        }
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
            case User::ROLE_SUPER_ADMIN:
                return $this->superAdminPermissions($deletor, $deletee);
        }

        return false;
    }

    /**
     * @param User $deletee
     * @return bool
     */
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

    /**
     * @param User $deletor
     * @param User $deletee
     * @return bool
     */
    private function superAdminPermissions(User $deletor, User $deletee): bool
    {
        switch ($deletee->getRoleName()) {
            case User::ROLE_LAY_DEPUTY:
            case User::ROLE_PA:
            case User::ROLE_PA_TEAM_MEMBER:
            case User::ROLE_PA_NAMED:
            case User::ROLE_PA_ADMIN:
            case User::ROLE_PROF:
            case User::ROLE_PROF_TEAM_MEMBER:
            case User::ROLE_PROF_NAMED:
            case User::ROLE_PROF_ADMIN:
                return true;
        }

        return $deletor->getRoleName() === User::ROLE_SUPER_ADMIN ? true : false;
    }
}

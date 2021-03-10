<?php declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const DELETE_USER = 'delete-user';
    const EDIT_USER = 'edit-user';
    const ADD_USER = 'add-user';

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
            case self::EDIT_USER:
            case self::ADD_USER:
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
     * Determine whether logged in user can delete a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
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
                return $this->paProfNamedAdminDeletePermissions($deletee);
            case User::ROLE_SUPER_ADMIN:
                return $this->superAdminDeletePermissions($deletor, $deletee);
        }

        return false;
    }

    private function paProfNamedAdminDeletePermissions(User $deletee): bool
    {
        switch ($deletee->getRoleName()) {
            case User::ROLE_LAY_DEPUTY:
            case User::ROLE_ADMIN:
            case User::ROLE_SUPER_ADMIN:
                return false;
        }

        return true;
    }

    private function superAdminDeletePermissions(User $deletor, User $deletee): bool
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

    /**
     * Determine whether logged in user can edit a subject user.
     *
     * Ensure any changes are mirrored in API/Client version of this class.
     *
     * @param User $deletor
     * @param User $deletee
     * @return bool
     */
    private function determineAddEditPermission(User $editor, User $editee)
    {
        if ($editor->getId() === $editee->getId()) {
            return true;
        }

        switch ($editor->getRoleName()) {
            case User::ROLE_SUPER_ADMIN:
                return true;
            case User::ROLE_ELEVATED_ADMIN:
               if ($editee->isSuperAdmin()) {
                   return false;
               }
               return true;
            case User::ROLE_ADMIN:
            case User::ROLE_AD:
                if (
                    $editee->isSuperAdmin() ||
                    $editee->isElevatedAdmin()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PA:
            case User::ROLE_PA_NAMED:
                if (
                    $editee->hasAdminRole() ||
                    $editee->isLayDeputy() ||
                    $editee->isProfDeputy()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PROF:
            case User::ROLE_PROF_NAMED:
            if (
                $editee->hasAdminRole() ||
                $editee->isLayDeputy() ||
                $editee->isPaDeputy()
            ) {
                return false;
            }
                return true;
            case User::ROLE_PA_ADMIN:
                if (
                    $editee->hasAdminRole() ||
                    $editee->isLayDeputy() ||
                    $editee->isPaNamedDeputy() ||
                    $editee->isPaTopRole() ||
                    $editee->isProfDeputy()
                ) {
                    return false;
                }
                return true;
            case User::ROLE_PROF_ADMIN:
                if (
                    $editee->hasAdminRole() ||
                    $editee->isLayDeputy() ||
                    $editee->isProfNamedDeputy() ||
                    $editee->isProfTopRole() ||
                    $editee->isPaDeputy()
                ) {
                    return false;
                }
                return true;
            default:
                return false;
        }
    }
}

<?php
namespace AppBundle\Security;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class OrganisationVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        // only vote on Organisation objects inside this voter
        if (!$subject instanceof Organisation) {
            return false;
        }

        return true;
    }

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
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canManage(Organisation $organisation, User $user)
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        foreach ($organisation->getUsers() as $member) {
            if ($member->getId() === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}

<?php
namespace AppBundle\Security;

use AppBundle\Entity\Organisation;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class OrganisationVoter extends Voter
{
    /** @var string */
    const VIEW = 'view';

    /** @var string */
    const EDIT = 'edit';

    /** @var Security  */
    private $security;

    /**
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, [self::VIEW, self::EDIT]) && $subject instanceof Organisation;
    }

    /**
     * @param string $attribute
     * @param Organisation $organisation
     * @param TokenInterface $token
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
     * @param Organisation $organisation
     * @param User $user
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

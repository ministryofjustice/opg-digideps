<?php

namespace AppBundle\Security;

use AppBundle\Entity\Note;
use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NoteVoter extends Voter
{
    const DELETE_NOTE = 'delete-note';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * NoteVoter constructor.
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
     * @param  string $attribute
     * @param  mixed  $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        switch ($attribute) {
            case self::DELETE_NOTE:
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

        if ($attribute === self::DELETE_NOTE) {
            return $this->determineDeletePermission($loggedInUser, $subject);
        }

        return false;
    }

    /**
     * Determine whether logged in user can delete a note
     *
     * @param  User $loggedInUser
     * @param  Note $note
     * @return bool
     */
    private function determineDeletePermission(User $loggedInUser, Note $note)
    {
        if ($loggedInUser->isDeputyPa()) {
            // ensure user is the opne who created the note
            if ($note->getCreatedBy()->getId() === $loggedInUser->getId()) {
                return true;
            }
        }

        return false;
    }
}

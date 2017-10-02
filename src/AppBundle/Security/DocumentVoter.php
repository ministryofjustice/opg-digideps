<?php

namespace AppBundle\Security;

use AppBundle\Entity\Client;
use AppBundle\Entity\Report\Report;
use AppBundle\Entity\Report\Document;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    const ADD_DOCUMENT = 'add-note';
    const DELETE_DOCUMENT = 'delete-document';

    /**
     * @var AccessDecisionManagerInterface
     */
    private $decisionManager;

    /**
     * DocumentVoter constructor.
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
            case self::ADD_DOCUMENT:
            case self::DELETE_DOCUMENT:
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
        /** @var User $loggedInUser */
        $loggedInUser= $token->getUser();

        if (!$loggedInUser instanceof User) {
            // the loggedUser must be logged in PA user; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::ADD_DOCUMENT:
                if ($subject instanceof Report) {
                    /** @var Report $subject */
                    return $subject->getClient()->hasUser($loggedInUser);
                }
                return false;
            case self::DELETE_DOCUMENT:
                if ($subject instanceof Document) {
                    $report = $subject->getReport();
                    if ($report instanceof Report) {
                        /** @var Note $subject */
                        return $report->getClient()->hasUser($loggedInUser);
                    }
                }
                return false;
        }

        return false;
    }

}

<?php

namespace OPG\Digideps\Backend\Security;

use OPG\Digideps\Backend\Domain\Deputy\DeputyType;
use OPG\Digideps\Backend\Domain\Report\ReportAccessService;
use OPG\Digideps\Backend\Entity\CourtOrder;
use OPG\Digideps\Backend\Entity\Deputy;
use OPG\Digideps\Backend\Entity\Organisation;
use OPG\Digideps\Backend\Entity\Report\Report;
use OPG\Digideps\Backend\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

/**
 * @extends Voter<string, Report>
 */
class ReportVoter extends Voter
{
    public const string ACCESS = 'access';

    public function __construct(private readonly Security $security, private readonly ReportAccessService $reportAccessService)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $this->supportsAttribute($attribute) && is_object($subject) && $this->supportsType(get_class($subject));
    }

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === self::ACCESS;
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Report::class, true);
    }

    /**
     * @param Report $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!($user instanceof User)) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return $this->supportsAttribute($attribute) && $this->canAccess($subject, $user);
    }

    private function canAccess(Report $report, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            //OPG Admin; allow access.
            return true;
        }

        return in_array($report->getId(), $this->reportAccessService->getVisibleReportIdsGivenUserId($user->getId()));
    }
}

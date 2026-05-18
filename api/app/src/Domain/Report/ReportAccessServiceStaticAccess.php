<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Kernel;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @deprecated
 */
#[Autoconfigure(public: true)]
final readonly class ReportAccessServiceStaticAccess
{
    public function __construct(
        private ReportAccessService $reportAccessService,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    /**
     * @return array<int>|null
     */
    private function wrap(?int $userId): ?array
    {
        if ($userId === null) {
            $user = $this->tokenStorage->getToken()?->getUser();
            if ($user instanceof User && !empty($user->getId())) {
                $userId = $user->getId();
            } else {
                return null;
            }
        }

        return $this->reportAccessService->getVisibleReportIdsGivenUserId($userId);
    }

    /**
     * @return array<int>
     */
    public static function getVisibleReportIdsGivenUserId(?int $userId): array
    {
        /**
         * @var Kernel $kernel
         */
        global $kernel;
        /**
         * @var ReportAccessServiceStaticAccess|null $service
         */
        $service = $kernel->getContainer()->get(ReportAccessServiceStaticAccess::class);
        return $service?->wrap($userId) ?? [];
    }
}

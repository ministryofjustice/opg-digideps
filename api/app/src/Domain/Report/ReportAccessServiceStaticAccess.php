<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Domain\Report;

use OPG\Digideps\Backend\Entity\User;
use OPG\Digideps\Backend\Kernel;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

final readonly class ReportAccessServiceStaticAccess
{
    /**
     * @return array<int>
     */
    public static function getVisibleReportIdsGivenUserId(?int $userId): array
    {
        /**
         * @var Kernel $kernel;
         */
        global $kernel;

        if ($userId === null) {
            /**
             * @var TokenStorage|null $storage
             */
            $storage = $kernel->getContainer()->get('security.token_storage');
            $user = $storage?->getToken()?->getUser();
            if ($user instanceof User) {
                $userId = $user->getId();
            }
        }

        if ($userId === null) {
            return [];
        }

        /**
         * @var ReportAccessService|null $service
         */
        $service = $kernel->getContainer()->get(ReportAccessService::class);
        return $service?->getVisibleReportIdsGivenUserId($userId) ?? [];
    }
}

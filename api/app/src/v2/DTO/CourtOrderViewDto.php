<?php

namespace App\v2\DTO;

final readonly class CourtOrderViewDto
{
    /**
     * @param DeputyItemDto[] $coDeputies
     */
    public function __construct(
        public string $clientFullName,
        public string $courtOrderUid,
        public string $courtOrderType,
        public string $courtOrderStatus,
        public ?string $reportType,
        public string $inviteUrl,
        public array $coDeputies,
    ) {
    }
}

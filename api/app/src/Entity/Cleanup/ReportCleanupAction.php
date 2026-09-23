<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Cleanup;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'report_cleanup_action')]
#[Entity]
class ReportCleanupAction
{
    public function __construct(
        #[Id, Column(name: 'report_id', type: 'integer', nullable: false)]
        public readonly int $reportId,
        #[Id, Column(name: 'order_id', type: 'integer', nullable: false)]
        public readonly int $orderId,
        #[Column(name: 'keep', type: 'boolean', nullable: false)]
        public readonly bool $keep
    ) {
    }
}

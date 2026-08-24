<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Cleanup;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use OPG\Digideps\Backend\Cleanup\Model\Problem;

#[Table(name: 'report_cleanup_problem')]
#[Entity]
class ReportCleanupProblem
{
    public function __construct(
        #[Column(name: 'client_id', type: 'integer', nullable: true)]
        public readonly ?int $clientId,
        #[Column(name: 'report_id', type: 'integer', nullable: true)]
        public readonly ?int $reportId,
        #[Column(name: 'order_id', type: 'integer', nullable: true)]
        public readonly ?int $orderId,
        #[Column(name: 'problem', type: 'integer', nullable: false, enumType: Problem::class)]
        public readonly Problem $problem,
        #[Id, Column(name: 'id', type: 'integer', nullable: true)]
        public readonly ?int $id = null
    ) {
    }
}

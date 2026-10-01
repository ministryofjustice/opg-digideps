<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Staging;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use OPG\Digideps\Common\CourtOrder\CourtOrderReportType;
use OPG\Digideps\Common\CourtOrder\CourtOrderType;
use OPG\Digideps\Common\Deputy\DeputyType;

#[Entity, Table(name: 'pa_pro_ingest', schema: 'staging')]
class StagingPaProIngest
{
    public function __construct(
        #[Column(name: 'case_number', type: 'string', length: 20)]
        public readonly string $caseNumber,
        #[\SensitiveParameter, Column(name: 'client_first_name', type: 'string', length: 50)]
        public readonly string $clientFirstName,
        #[\SensitiveParameter, Column(name: 'client_last_name', type: 'string', length: 50)]
        public readonly string $clientLastName,
        #[\SensitiveParameter, Column(name: 'client_date_of_birth', type: 'date_immutable')]
        public readonly \DateTimeImmutable $clientDateOfBirth,
        #[\SensitiveParameter, Column(name: 'client_address1', type: 'string', length: 200)]
        public readonly string $clientAddress1,
        #[\SensitiveParameter, Column(name: 'client_address2', type: 'string', length: 200)]
        public readonly string $clientAddress2,
        #[\SensitiveParameter, Column(name: 'client_address3', type: 'string', length: 200)]
        public readonly string $clientAddress3,
        #[\SensitiveParameter, Column(name: 'client_address4', type: 'string', length: 200)]
        public readonly string $clientAddress4,
        #[\SensitiveParameter, Column(name: 'client_address5', type: 'string', length: 200)]
        public readonly string $clientAddress5,
        #[\SensitiveParameter, Column(name: 'client_post_code', type: 'string', length: 10)]
        public readonly string $clientPostCode,
        #[Column(name: 'deputy_type', type: 'string', length: 3, enumType: DeputyType::class)]
        public readonly DeputyType $deputyType,
        #[Column(name: 'deputy_uid', type: 'string', length: 20)]
        public readonly string $deputyUid,
        #[\SensitiveParameter, Column(name: 'deputy_email', type: 'string', length: 60)]
        public readonly string $deputyEmail,
        #[Column(name: 'deputy_organisation', type: 'string', length: 100)]
        public readonly string $deputyOrganisation,
        #[\SensitiveParameter, Column(name: 'deputy_first_name', type: 'string', length: 100)]
        public readonly string $deputyFirstName,
        #[\SensitiveParameter, Column(name: 'deputy_last_name', type: 'string', length: 100)]
        public readonly string $deputyLastName,
        #[\SensitiveParameter, Column(name: 'deputy_address1', type: 'string', length: 200)]
        public readonly string $deputyAddress1,
        #[\SensitiveParameter, Column(name: 'deputy_address2', type: 'string', length: 200)]
        public readonly string $deputyAddress2,
        #[\SensitiveParameter, Column(name: 'deputy_address3', type: 'string', length: 200)]
        public readonly string $deputyAddress3,
        #[\SensitiveParameter, Column(name: 'deputy_address4', type: 'string', length: 200)]
        public readonly string $deputyAddress4,
        #[\SensitiveParameter, Column(name: 'deputy_address5', type: 'string', length: 200)]
        public readonly string $deputyAddress5,
        #[\SensitiveParameter, Column(name: 'deputy_post_code', type: 'string', length: 10)]
        public readonly string $deputyPostCode,
        #[Column(name: 'made_date', type: 'date_immutable')]
        public readonly \DateTimeImmutable $madeDate,
        #[Column(name: 'report_type', type: 'string', length: 6, enumType: CourtOrderReportType::class)]
        public readonly CourtOrderReportType $reportType,
        #[Column(name: 'order_type', type: 'string', length: 3, enumType: CourtOrderType::class)]
        public readonly CourtOrderType $orderType,
        #[Id, GeneratedValue, Column(type: 'integer')]
        public int $id = 0,
    ) {
    }
}

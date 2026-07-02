<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Sirius;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity(readOnly: true), Table(name: 'sirius_client', schema: 'staging')]
class SiriusClient
{
    public function __construct(
        #[Id, Column(name: 'case_number', type: 'string', length: 20)]
        public readonly string $caseNumber,
        #[\SensitiveParameter, Column(name: 'client_first_name', type: 'string', length: 50)]
        public readonly string $clientFirstName,
        #[\SensitiveParameter, Column(name: 'client_last_name', type: 'string', length: 50)]
        public readonly string $clientLastName,
        #[\SensitiveParameter, Column(name: 'client_date_of_birth', type: 'date_immutable', nullable: true)]
        public readonly ?\DateTimeImmutable $clientDateOfBirth,
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
        #[Column(name: 'local_id', type: 'integer', nullable: true)]
        public ?int $localId,
    ) {}
}

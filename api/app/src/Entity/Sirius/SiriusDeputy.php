<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Sirius;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use OPG\Digideps\Common\Deputy\DeputyType;

#[Entity(readOnly: true), Table(name: 'sirius_deputy', schema: 'staging')]
class SiriusDeputy
{
    public function __construct(
        #[Id, Column(name: 'deputy_uid', type: 'string', length: 20)]
        public readonly string $deputyUid,
        #[Column(name: 'deputy_type', type: 'string', length: 3, enumType: DeputyType::class)]
        public readonly DeputyType $deputyType,
        #[\SensitiveParameter, Column(name: 'deputy_email', type: 'string', length: 60, nullable: true)]
        public readonly ?string $deputyEmail,
        #[Column(name: 'deputy_organisation', type: 'string', length: 100, nullable: true)]
        public readonly ?string $deputyOrganisation,
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
        #[Column(name: 'local_id', type: 'integer', nullable: true)]
        public ?int $localId,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\Sirius;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Entity(readOnly: true), Table(name: 'sirius_organisation', schema: 'staging')]
class SiriusOrganisation
{
    public function __construct(
        #[Id, Column(name: 'domain', type: 'string', length: 60)]
        public readonly string $domain,
        #[\SensitiveParameter, Column(name: 'name', type: 'string', length: 256, nullable: true)]
        public readonly ?string $name,
        #[Column(name: 'local_id', type: 'integer', nullable: true)]
        public ?int $localId,
    ) {
    }
}

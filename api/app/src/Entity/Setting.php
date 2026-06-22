<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

#[ORM\Table(name: 'setting')]
#[ORM\Entity]
class Setting
{
    public function __construct(
        #[JMS\Type('string')]
        #[JMS\Groups(['setting'])]
        #[ORM\Column(name: 'id', type: 'string', length: 64, nullable: false)]
        #[ORM\Id]
        private string $id,
        #[JMS\Type('string')]
        #[JMS\Groups(['setting'])]
        #[ORM\Column(name: 'content', type: 'text', nullable: false)]
        private string $content,
        #[JMS\Type('boolean')]
        #[JMS\Groups(['setting'])]
        #[ORM\Column(name: 'enabled', type: 'boolean', nullable: false)]
        private bool $enabled
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }


    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}

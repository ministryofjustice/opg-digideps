<?php

declare(strict_types=1);

namespace OPG\Digideps\Backend\Entity\UserResearch;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: 'electronic_device_type')]
#[ORM\Entity]
class ElectronicDeviceType
{
    public function __construct(array $formResponses, ?Uuid $id = null)
    {
        if (empty($formResponses)) {
            throw new \RuntimeException('Must select at least one electronic device type', 403);
        }

        $this->id = $id ?? Uuid::v4();

        $setters = array_map(function ($response): string {
            if (!is_string($response)) {
                throw new \InvalidArgumentException('Electronic device type must be a string');
            }
            return sprintf('set%s', ucfirst($response));
        }, $formResponses);

        foreach ($setters as $setter) {
            $this->$setter(true);
        }
    }

    #[JMS\Type('OPG\Digideps\Backend\Entity\UserResearchResponse')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\OneToOne(targetEntity: UserResearchResponse::class, mappedBy: 'electronicDeviceType', cascade: ['persist'])]
    private UserResearchResponse $userResearchResponse;

    #[JMS\Type('string')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\CustomIdGenerator(class: Uuid::class)]
    private Uuid $id;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'desktop', type: 'boolean', nullable: true)]
    private ?bool $desktop = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'laptop', type: 'boolean', nullable: true)]
    private ?bool $laptop = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'mobile', type: 'boolean', nullable: true)]
    private ?bool $mobile = null;

    #[JMS\Type('boolean')]
    #[JMS\Groups(['user-research', 'satisfaction'])]
    #[ORM\Column(name: 'tablet', type: 'boolean', nullable: true)]
    private ?bool $tablet = null;

    public function getUserResearchResponse(): UserResearchResponse
    {
        return $this->userResearchResponse;
    }

    public function setUserResearchResponse(UserResearchResponse $userResearchResponse): ElectronicDeviceType
    {
        $this->userResearchResponse = $userResearchResponse;

        return $this;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getDesktop(): ?bool
    {
        return $this->desktop;
    }

    public function setDesktop(?bool $desktop): ElectronicDeviceType
    {
        $this->desktop = $desktop;

        return $this;
    }

    public function getLaptop(): ?bool
    {
        return $this->laptop;
    }

    public function setLaptop(?bool $laptop): ElectronicDeviceType
    {
        $this->laptop = $laptop;

        return $this;
    }

    public function getMobile(): ?bool
    {
        return $this->mobile;
    }

    public function setMobile(?bool $mobile): ElectronicDeviceType
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getTablet(): ?bool
    {
        return $this->tablet;
    }

    public function setTablet(?bool $tablet): ElectronicDeviceType
    {
        $this->tablet = $tablet;

        return $this;
    }
}
